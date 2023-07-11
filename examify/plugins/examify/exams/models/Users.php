<?php namespace Examify\Exams\Models;


use \Rainlab\User\Models\User as RainLabUser;
use Validator;
use Auth;
use Mail;
use Str;
use Request;

use \RainLab\User\Components\Account as RainLabAccountComponent;
use \Examify\Exams\Models\UsersSubscriptions as UsersSubscriptions;
use \Examify\Exams\Models\Courses as Courses;
use \Examify\Exams\Models\UsersSettings as UsersSettings;
use Carbon\Carbon;
use \Examify\Exams\Models\Schools as Schools;
use \Examify\Exams\Models\Licenses as Licences;
use \Examify\Exams\Models\Classes as Classes;
use \Backend\Models\User as BackendUser;

use \Examify\Exams\Models\PracticeSessions as PracticeSessions;
use Exception;

/**
 * Users Model
 */
class Users extends RainLabUser
{
    /**
     * @var string The database table used by the model.
     */

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [
        'name',
        'surname',
        'login',
        'username',
        'email',
        'password',
        'password_confirmation'
    ];

    /**
     * @var array Relations
     */
    public $hasMany = [
      'practiceSessions' => ['Examify\Exams\Models\PracticeSessions', 'key' => 'user_id' ],
      'subscriptions' => ['Examify\Exams\Models\UsersSubscriptions', 'key' => 'user_id'],
      'settings' => ['Examify\Exams\Models\UsersSettings', 'key' => 'user_id'],
      'licences' => ['Examify\Exams\Models\Licenses', 'key' => 'user_id'],
      'orders' => ['Examify\Exams\Models\Orders', 'key' => 'user_id']
    ];

    public $belongsToMany = [
      'schools' => ['Examify\Exams\Models\Schools', 'table' => 'examify_exams_schools_users', 
                    'pivot' => [
                      'is_school_admin', 'is_teacher'
                    ],
                    'key' => 'user_id',
                    'otherKey' => 'school_id'
                  ],
      'classes' => [
          'Examify\Exams\Models\Classes',
          'table' => 'examify_exams_classes_users',
          'pivot' => 'is_teacher',
          'key'       => 'user_id',
          'otherKey'  => 'class_id' 
      ]
    ];

    public function licences()
    {
      return $this->hasMany('Examify\Exams\Models\Licenses', 'user_id');
    }

    public function userSettings()
    {
      return $this->hasMany('Examify\Exams\Models\UsersSettings', 'user_id');
    }

    public function homeworkSessions()
    {
      return $this->hasMany('Examify\Exams\Models\PracticeSessions', 'user_id')->where('homework_id', '!=', null)->where('homework_id', '>', 0)->where('deleted', '!=', 1)->orderByDesc('due_date');
    }

    public function getHomeworkSessions()
    {
      return $this->homeworkSessions()->with('homework')->get();
    }

    public function practiceSessions()
    {
      return $this->hasMany('Examify\Exams\Models\PracticeSessions', 'user_id')->where('deleted', false)->orderByDesc('created_at');
    }

    public function getUserSetting($field)
    {
      $result = $this->settings()->where('field', $field)->get()->pluck('value');
      if(count($result) == 1){
        return $result->first();
      }
      else {
        return [];
      }
    }

    public function getLastHomeworkPracticeSessionForClass($classid)
    {
      // get all the homework exercises for this class
      $ps = $this->getHomeworkSessions()->where('finished', true);

      if(!$ps->count()){ return false; }

      return $ps->where('homework.class_id', $classid)->sortByDesc('id')->first();

    }

    public function isAdmin()
    {
      return $this->getUserSetting('isAdmin') ? true : $this->isSuperAdmin();
    }
    public function isSuperAdmin()
    {
      return $this->getUserSetting('isSuperAdmin') ? true : false;
    }

    public function makeSchoolAdminForSchool($schoolid, $admin = true)
    {
      $this->schools()->updateExistingPivot($schoolid, ['is_teacher' => true, 'is_school_admin' => $admin]);
    }

    public function getLicensedCoursesForYearAndLevel($year = [], $level = [])
    {
      if(empty($year)){
        $year = Classes::getCurrentYear();
      }

      if(empty($level)){
        $level = $this->getUserSetting('level');
      }

      // might go wrong otherwise in the courses where statement
      $level = strtolower($level);

      if($this->isSuperAdmin()){ return Courses::orderBy('name')->get(); }
      if($this->isAdmin()){ return Courses::orderBy('name')->get(); }
      if($this->hasBackendUser()){ return Courses::orderBy('name')->get(); }

      // in case it is a teacher, show also the other levels for the courses it is teacher for
      $teachercourses = collect([]);
      if($this->isTeacher($year)){
        $classes = $this->classes()->with('course')->wherePivot('is_teacher', true)->get();
        if($classes->count()){
          // get the courses
          $coursename = Courses::find($classes->pluck('course')->pluck('id')->values()->all())->pluck('name');
          $teachercourses = Courses::whereIn('name', $coursename)->get();
        }
      }

      $mylics = Licences::where('user_id', $this->id)
                        ->where('activated', true)
                        ->where('schoolyear', $year)
                        ->where('course_id', '>', 0)
                        ->orderBy('course_id')
                        ->get();

      return Courses::find($mylics->pluck('course_id'))->where('level', $level)->merge($teachercourses)->sortBy('name');

    }

    public function getUnlicensedCoursesForYearAndLevel($year = [], $level = [])
    {
      if(empty($year)){
        $year = Classes::getCurrentYear();
      }

      if(empty($level)){
        $level = $this->getUserSetting('level');
      }

      $level = strtolower($level);

      $courses = $this->getLicensedCoursesForYearAndLevel($year, $level);

      if(!$courses->count()){ 
        return Courses::where('level', $level)->orderBy('name')->get();
      }

      return Courses::where('level', $level)->whereNotIn('id', $courses->pluck('id')->values()->all())->orderBy('name')->get();
    }

    public function isTeacher($schoolyear = [])
    {
      if($this->isAdmin())
      {
        return true;
      }

      if(empty($schoolyear)){
        $schoolyear = Classes::getCurrentYear();
      }

      // in case this user is admin or super admin
      // get for the current schoolyear
      return $this->schools()->wherePivot('is_teacher', true)->wherePivot('schoolyear', $schoolyear)->get()->count() > 0;
    }

    public function isTeacherForSchool($school_id, $year)
    {
      $schools = $this->schools()->wherePivot('is_teacher', true)->wherePivot('schoolyear', $year)->get();
      if(!$schools->count())
      {
        return false;
      }

      // check if the school id is in
      return $schools->pluck('id')->contains($school_id);
    }

    public function isStudentForSchool($school_id, $year)
    {
      $schools = $this->schools()->wherePivot('is_teacher', false)->wherePivot('schoolyear', $year)->get();
      if(!$schools->count())
      {
        return false;
      }

      // check if the school id is in
      return $schools->pluck('id')->contains($school_id);
    }

    public function makeStudentForSchool($school_id, $year, $active = true, $sendmail = false)
    {
      if($this->isStudentForSchool($school_id, $year))
      {
        return;
      }

      // add it
      $this->schools()->attach($school_id, ['is_teacher' => false, 'schoolyear' => $year]);

      $newLic = new Licences();

      // set the properties
      $newLic->is_teacher = false;
      $newLic->school_id = $school_id;
      $newLic->generateKey();
      $newLic->schoolyear = $year;
      $newLic->user_id = $this->id;

      $newLic->activated = $active;
      $newLic->save();

      if($sendmail)
      {
        $user = $this;
        $school = Schools::find($school_id);

        $data = [
          'name' => $this->name,
          'schoolname' => $school->name
        ];

        Mail::queue('examify.exams::mail.school_student_coupled', $data, function($message) use ($user) {
          $message->to($user->email, $user->name . ' ' . $user->surname);
          //$message->bcc('accounts@examify.nl', 'Account Registration');
        });
      }

    }

    public function makeTeacherForSchool($school_id, $year, $active = true, $sendmail = false)
    {

      if($this->isTeacherForSchool($school_id, $year))
      {
        return;
      }

      // add it
      $this->schools()->attach($school_id, ['is_teacher' => true, 'schoolyear' => $year]);

      $newLic = new Licences();

      // set the properties
      $newLic->is_teacher = true;
      $newLic->school_id = $school_id;
      $newLic->generateKey();
      $newLic->schoolyear = $year;
      $newLic->user_id = $this->id;

      $newLic->activated = $active;
      $newLic->save();

    }

    public function getAdminClassesGroupedBySchoolAndCourses()
    {

      // get the classes this user administers


    }

    public function hasBackendUser()
    {
      $backenduser = BackendUser::where('email', $this->email)->get();
      return $backenduser->count() > 0;
    }

    // get the licences for year and course id
    public function hasLicenceForCourseIdAndYear($courseid, $year)
    {

      // in case this is a superuser 
      if($this->isTeacher())
      {
        return true;
      }

      if($this->hasBackendUser()){
        return true;
      }

      $myLic = Licences::where('user_id', $this->id)
                        ->where('activated', true)
                        ->where('schoolyear', $year)
                        ->where('course_id', $courseid)
                        ->get();

      return $myLic->count() > 0;
    }

    public function getPracticeSessionsForClass($class)
    {
      // get all the homework items for this class
      $hw = $class->getHomework(+1);
      $hw = $hw->merge($class->getHomework(-1));

      if($hw->count() == 0){ return collect([]); }

      $ids = $hw->pluck('id')->values();
      
      // get all practice sessions for this user where one of these ids are the homework id
      return PracticeSessions::where('user_id', $this->id)->whereIn('homework_id', $ids)->get();


    }

    // check that it is admin for this school
    public function isAdminForSchool($school_id)
    {
      $myschools = $this->getSchoolAdmin();

      if(!$myschools->count()){
        return false;
      }

      // in case the user is a superuser, it is also admin
      if($this->getUserSetting('isAdmin'))
      {
        return true;
      }

      // check if the id is part of this
      return $myschools->pluck('id')->contains($school_id);
    }

    // check if the user is a teacher for this class
    public function isTeacherForClass($class_id)
    {

      // get the classes where this user is teacher for
      $classes = $this->classes()->wherePivot('is_teacher', true)->get();
      if(!$classes->count())
      {
        return false;
      }

      // check if the class id is in
      return $classes->pluck('id')->contains($class_id);
    }

    public function getSchoolAdmin()
    {

      if($this->isAdmin())
      {
        $schoolid = $this->getUserSetting('schoolid');
        if($schoolid){
          return Schools::where('id', $schoolid)->get();
        }
        else {
          return Schools::all();
        }
      }

      return $this->schools()->wherePivot('is_school_admin', true)->get();

    }

    public function hasSchoolSelected()
    {
      if($this->isAdmin()){
       return !!$this->getUserSetting('schoolid');
      }
      else {
        return true;
      }
    }

    public function getSchoolTeacher($all = false)
    {

      // NOTE THAT THE "ALL" INPUT IS FOR THE NAVBAR
      // TO SHOW AT LEAST ALL THE SCHOOLS

      // get the schools where this user is admin for
      if($this->isAdmin()){

        $schoolid = $this->getUserSetting('schoolid');

        if(!$all && $schoolid){
          return Schools::where('id', $schoolid)->get();
        }
        else {
          return Schools::all();
        }

      }

      return $this->schools()->wherePivot('is_teacher', true)->get()->unique();
    }

    public function isSchoolAdmin()
    {

      if($this->getUserSetting('isAdmin')){ 
        return true;
      }

      // return true if it is somehow a school admin
      $schools = $this->schools()->wherePivot('is_school_admin', true)->get();
      return $schools->count() > 0;

    }

    public function setUserSetting($field, $value){
      $setting = UsersSettings::firstOrNew(array('user_id' => $this->id, 'field' => $field));
      $setting->value = $value;
      $setting->save();
    }

    public static function getRootUser()
    {
      $user = Auth::getUser();
      if(!$user){
        return $user;
      }

      return Users::find($user->id);
    }

    // get the user
    public static function getUser()
    {
      $user = Auth::getUser();
      if(!$user){
        return $user;
      }

      $user = Users::find($user->id);

      if(!$user->isSuperAdmin()){
        return $user;
      }

      if($user->getUserSetting('impersonate')){
        return Users::find($user->getUserSetting('impersonate'));
      }

      return $user;

    }

    public function subscriptions(){
      return $this->hasMany('Examify\Exams\Models\UsersSubscriptions', 'user_id')->where('created_at', '>=', Carbon::now()->subYear());
    }

    // get the subscribed courses
    public function getSubscribedCourses()
    {
      // in case it is a teacher, all courses are subscribed
      if($this->isSchoolAdmin()){
        return Courses::all();
      }

      // also a teacher can see the contact for all courses
      if($this->isTeacher())
      {
        return Courses::all();
      }

      if($this->hasBackendUser()){
        return Courses::all();
      }

      // check the licences for this year
      $userLicences = Licences::where('user_id', $this->id)
              ->where('activated', true)
              ->where('schoolyear', Classes::getCurrentYear())
              ->where('course_id', '>', 0)
              ->get();

     // pluck all the course ids
     if(!$userLicences->count())
     {
      return collect([]);
     }

     $course_ids = $userLicences->pluck('course_id')->unique()->values()->all();

     // select the courses
     return Courses::find($course_ids);

    }

    public function hasPracticeSession($PSID)
    {
      if(!$user = Users::getUser()){
        return false;
      }

      // check if this practice session belongs to homework, and this user is a teacher for this homework item
      $ps = PracticeSessions::find($PSID);
      if(!$ps){ return false; }

      if($user->getUserSetting('isSuperAdmin')){ return $ps; }

      $hw = $ps->homework;
      if($hw && $hw->class->hasTeacher($user)){ return $ps; }
    
      if($ps->user_id != $user->id){ return false; }

      return $ps;

    }

    public function hasActivePracticeSession($PSID)
    {
      if(!$user = Users::getUser()){
        return false;
      }

      $myPracticeSession = PracticeSessions::where('id', $PSID)
                                              ->where('user_id', $user->id)
                                              ->where('finished', false)->first();

      if(!$myPracticeSession){
        return false;
      }

      return $myPracticeSession;

    }

    public static function validateRegistration()
    {
        // the result array
        $myresultarray = [];

        // check the e-mail
        $email = post('email');

        $validator = Validator::make([ 'tovalidate' => $email ], [ 'tovalidate' => 'required|email' ]);

        if($validator->fails()){
          $myresultarray['email'] = [
            'valid' => false,
            'message' => 'Dit is geen geldig e-mail adres.'
          ];
        }
        else {
          // check if is unique
          $isTaken = Auth::findUserByLogin(post('email')) ? 1 : 0;
          $myresultarray['email'] = [
            'valid' => !$isTaken,
            'message' => 'Dit e-mail adres is al in gebruik.'
          ];
        }

        // check the first name
        $validator = Validator::make([ 'tovalidate' => post('firstname') ], [ 'tovalidate' => 'required' ]);

        $myresultarray['firstname'] = [
          'valid' => !$validator->fails(),
          'message' => 'Vul een voornaam in.'
        ];

        // check the last name
        $validator = Validator::make([ 'tovalidate' => post('surname') ], [ 'tovalidate' => 'required' ]);

        $myresultarray['surname'] = [
          'valid' => !$validator->fails(),
          'message' => 'Vul een achternaam in.'
        ];

        // check the password
        $validator = Validator::make([ 'tovalidate' => post('password') ], [ 'tovalidate' => 'required|min:6' ]);

        $myresultarray['password'] = [
          'valid' => !$validator->fails(),
          'message' => 'Wachtwoord moet minimaal 6 karakters lang zijn.'
        ];

        // opleidingsniveau
        $validator = Validator::make([ 'tovalidate' => post('level') ], [ 'tovalidate' => 'required' ]);

        $myresultarray['level'] = [
          'valid' => !$validator->fails(),
          'message' => 'Selecteer een opleidingsniveau.'
        ];

        $allvalid = true;

        foreach($myresultarray as $entry){
          if($entry['valid'] == false){
            $allvalid = false;
            break;
          }
        }

        return [ 
          'inputvalidation' => $myresultarray,
          'allvalid' => $allvalid
        ];
    }

    public static function validateUpdate()
    {

        // the result array
        $myresultarray = [];

        // redirect to login page if no valid user found
        if(!$user = Users::getUser()){
            return [
                'allvalid' => false,
                'redirect' => '/login'
            ];
        }

        // get the daata
        $data = post();
        if (!array_key_exists('login', $data)) {
            $data['login'] = post('username', post('email'));
        }

        // check if the current password is empty
        if(empty($data['password_current'])){
          $myresultarray['password_current'] = [
            'valid' => false,
            'message' => 'Vul je huidige wachtwoord in ter controle.'
          ];
        }
        else {

          // setup the credentials
          $credentials = [
              'login'    => array_get($data, 'login'),
              'password' => array_get($data, 'password_current')
          ];

          try {
            $checkuser = Auth::authenticate($credentials, false);
          }
          catch (Exception $e){
            $myresultarray['password_current'] = [
              'valid' => false,
              'message' => 'Dit wachtwoord is onjuist.'
            ];
          }
        }

        // check if either the password or password confirmation is not empty
        if(!empty($data['password']) || !empty($data['password_confirmation'])){

            // validate that the two passwords are the same
            if($data['password'] != $data['password_confirmation']){
              $myresultarray['password'] = [
                  'valid' => false,
                  'message' => 'De twee wachtwoorden moeten overeen komen.'
              ];
              $myresultarray['password_confirmation'] = [
                  'valid' => false,
                  'message' => 'De twee wachtwoorden moeten overeen komen.'
              ];
            }

            // check the password
            $validator = Validator::make([ 'tovalidate' => post('password') ], [ 'tovalidate' => 'required|min:6' ]);

            if($validator->fails()){
              $myresultarray['password'] = [
                'valid' => false,
                'message' => 'Wachtwoord moet minimaal 6 karakters lang zijn.'
              ];
            }

        }

        // check the last name
        $validator = Validator::make([ 'tovalidate' => post('surname') ], [ 'tovalidate' => 'required' ]);

        if($validator->fails()){
          $myresultarray['surname'] = [
            'valid' => false,
            'message' => 'Vul een achternaam in.'
          ];
        }

        // check the name
        $validator = Validator::make([ 'tovalidate' => post('name') ], [ 'tovalidate' => 'required' ]);

        if($validator->fails()){
          $myresultarray['name'] = [
            'valid' => false,
            'message' => 'Vul een naam in.'
          ];
        }

        $validator = Validator::make([ 'tovalidate' => $data['email'] ], [ 'tovalidate' => 'required|email' ]);

        if($validator->fails()){
          $myresultarray['email'] = [
            'valid' => false,
            'message' => 'Dit is geen geldig e-mail adres.'
          ];
        }

        $allvalid = true;

        foreach($myresultarray as $entry){
          if($entry['valid'] == false){
            $allvalid = false;
            break;
          }
        }

        return [ 
          'inputvalidation' => $myresultarray,
          'allvalid' => $allvalid
        ];

    }

    public static function validateLogin()
    {
        /*
         * Validate input
         */

        // check the e-mail
        $email = post('email');

        $validator = Validator::make([ 'tovalidate' => $email ], [ 'tovalidate' => 'required|email' ]);

        if($validator->fails()){
          $myresultarray['email'] = [
            'valid' => false,
            'message' => 'Dit is geen geldig e-mail adres.'
          ];
        }
        else {
          // check if the mail adress is now
          $user = RainLabUser::where('email', post('email'))->first();
          if(!$user){
            $myresultarray['email'] = [
              'valid' => false,
              'message' => 'Dit e-mail adres is niet bekend bij ons.'
            ];
          }
          elseif(!$user->is_activated){
            $myresultarray['email'] = [
              'valid' => false,
              'message' => 'Dit e-mail adres is nog niet geactiveerd. Doe dat eerst.'
            ];
          }
          elseif(Auth::findThrottleByUserId($user->id, Request::ip())->checkSuspended())
          {
            $myresultarray['email'] = [
              'valid' => false,
              'message' => 'Dit e-mail adres is tijdelijk geblokkeerd vanwege te veel foute inlog pogingen. Je kunt via "Help" hieronder een nieuw wachtwoord instellen en de blokkade ongedaan maken.'
            ];
          }
          else {
            $myresultarray['email'] = [
              'valid' => true,
              'message' => ''
            ];
          }
        }

        $allvalid = true;

        foreach($myresultarray as $entry){
          if($entry['valid'] == false){
            $allvalid = false;
            break;
          }
        }

        return [
          'inputvalidation' => $myresultarray,
          'allvalid' => $allvalid
        ];

    }

    public function generatePassword()
    {
        $this->password = $this->password_confirmation = Str::random(static::getMinPasswordLength());
    }
}
