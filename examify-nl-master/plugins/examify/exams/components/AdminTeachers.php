<?php namespace Examify\Exams\Components;

use Cms\Classes\ComponentBase;
use \Examify\Exams\Models\Schools as Schools;
use \Examify\Exams\Models\Users as Users;
use \Examify\Exams\Models\Licenses as Licences;
use \Examify\Exams\Models\Classes as Classes;
use \Examify\Exams\Models\Courses as Courses;
use Input;
use Mail;
use Str;

class AdminTeachers extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'AdminTeachers Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return [
            'school_id' => [
                'title'         => 'School ID',
                'description'   => 'School ID',
                'default'       => 0,
                'type'          => 'string',
                'validationPattern'     => '^[0-9]+$',
                'validationMessage'     => 'School ID should be a numeric value'
            ]
        ];
    }

    function onRender()
    {

        // get the current school
        $school = Schools::find($this->property('school_id'));

        if(!$school->count()){
            return;
        }

        $user = Users::getUser();
        
        if(!$user || !$user->isAdminForSchool($school->id))
        {
          return redirect('/login')->with('redirect-to', url()->current());
        }

        // set the school
        $this->page['school'] = $school;

        // get the teachers
        $this->page['teachers'] = $school->getTeachers($this->param('year'));

        // also find the unactivated licences belonging to this school
        $this->page['inactiveLics'] = $school->getInactiveLicences($this->param('year'));

        $this->page['school_id'] = $school->id;
        $this->page['year'] = $this->param('year');
        $this->page['currentyear'] = Classes::getCurrentYear();
        $this->page['iscurrentyear'] = $this->page['year'] == $this->page['currentyear'];

    }

    function onImportFromLastYear()
    {
        // get the school_id
        $schoolid = input('school_id');

        // validate that the user is admin of this school
        $user = Users::getUser();

        if(!$user || !$user->isAdminForSchool($schoolid)){
            return [
                'valid' => false,
                'message' => 'Je bent niet meer ingelogd of hebt geen rechten meer voor deze school.',
            ];
        }

        // get the school
        $school = Schools::find($schoolid);  


        // get all the teachers for which this holds
        $teachers = input('include_teacher', false);
        if(!$teachers){
            return [
                'valid' => false,
                'message' => 'Selecteer minstens 1 leraar.'
            ];
        }

        $uids = array_keys($teachers);

        // loop over the uids, get the user account and make it a teacher for this school
        $users = Users::find($uids);
        $cyear = Classes::getCurrentYear();
        foreach($users as $u){
           $u->makeTeacherForSchool($school->id, $cyear, true, false);
        }

        return [
            'valid' => true,
            'message-info' => 'De leraren zijn toegevoegd!',
            'updateElement' => [
                '#list-of-teachers-' . $school->id => $this->renderPartial('examifyHelpers/portal/listOfTeachers', [ 'teachers' => $school->getTeachers($this->param('year')), 
                    'inactiveLics' => $school->getInactiveLicences($this->param('year')),
                    'school_id' => $school->id,
                    'year' => $this->param('year'),
                    'iscurrentyear' => $this->param('year') == Classes::getCurrentYear(),
                    'admin_changes' => true ])
            ]
        ];

    }

    function onImport()
    {

        // get the school_id
        $schoolid = input('school_id');

        // get the extra data
        $extraData = input('extraData', false);

        if(!($extraData))
        {
            return [
                'valid' => false,
                'message' => 'Er is iets verkeerd gegaan.',
                'data' => input()
            ];
        }

        $extraData = json_decode($extraData, true);

        // 
        $CONFIRMATION = (int) $extraData['confirmation'];

        // validate that the user is admin of this school
        $user = Users::getUser();

        if(!$user || !$user->isAdminForSchool($schoolid)){
            return [
                'valid' => false,
                'message' => 'Je bent niet meer ingelogd of hebt geen rechten meer voor deze school.',
            ];
        }

        // get the school
        $school = Schools::find($schoolid);   

        // check if the import csv is specified
        if(!Input::hasFile('import_csv'))
        {
            return [
                'valid' => false,
                'message' => 'Selecteer een import csv file.'
            ];
        }

        // get the file
        $importfile = Input::file('import_csv');

        if(!$importfile->isValid())
        {
            return [
                'valid' => false,
                'message' => 'Dit is geen geldige import file.'
            ];
        }

        // validate this is a csv file
        $mimes = array('application/vnd.ms-excel','text/plain','text/csv','text/tsv');
        if(!in_array($importfile->getMimeType(),$mimes)){
            return [
                'valid' => false,
                'message' => 'Dit is geen csv bestand.'
            ];
        }

        $thislevel = 'vwo';
        
        // also define the coursename and the year
        $yearToLicence = input('yearToLicence', false);

        if(!$yearToLicence)
        {
            return [
                'valid' => false,
                'message' => 'Geef een schooljaar aan.'
            ];
        }

        if($CONFIRMATION == 0)
        {

            $delimiter = ",";

            // read the first line of the file to get the column names
            $f = fopen($importfile->getRealPath(), "r");
            $firstline = fgetcsv($f, 1000, $delimiter);
            fclose($f);

            // fallback
            if($firstline === FALSE || count($firstline) == 1){
                $delimiter = ";";
                $f = fopen($importfile->getRealPath(), "r");
                $firstline = fgetcsv($f, 1000, $delimiter);
                fclose($f);
            }

            // generate the items from here
            $settingslist = [
                [
                    'column' => 'Voornaam',
                    'index' => 0,
                    'name' => $firstline[0]
                ],
                [
                    'column' => 'Tussenvoegsel',
                    'index' => 1,
                    'name' => $firstline[1]
                ],
                [
                    'column' => 'Achternaam',
                    'index' => 2,
                    'name' => $firstline[2]
                ],
                [
                    'column' => 'E-mail',
                    'index' => 3,
                    'name' => $firstline[3]
                ],
                [
                    'column' => 'Niveau',
                    'index' => 4,
                    'isoptional' => true
                ],
                [
                    'column' => 'Klas',
                    'index' => 5,
                    'isoptional' => true
                ],
                [
                    'column' => 'Vak',
                    'index' => 6,
                    'isoptional' => true
                ]
            ];

            return [
                'valid' => true,
                'updateElement' => [
                    '#import-settings-school-' . $school->id => $this->renderPartial('examifyHelpers/portal/csvImportSettings', [
                        'settingslist' => $settingslist,
                        'columnnames' => $firstline,
                        'delimiter' => $delimiter
                    ])
                ],
                'hideElement' => [
                    '#import-button-confirm-school-' . $school->id
                ]
            ];
        }
        else {
            $columnidxvoornaam = input('csv-settings-0');
            $columnidxtussenvoegsel = input('csv-settings-1');
            $columnidxachternaam = input('csv-settings-2');
            $columnidxemail = input('csv-settings-3');
            $columnidxlevel = input('csv-settings-4');
            $columnidxclass = input('csv-settings-5');
            $columnidxcourse = input('csv-settings-6');
            $delimiter = input('delimiter', ',');
        }



        // read the file
        $row = 0;
        $output = [];
        $skipped = [];
        $skipfirst = true;
        if (($handle = fopen($importfile->getRealPath(), "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {

                // skip the first one
                if($skipfirst)
                {
                    $skipfirst = false;
                    continue;
                }

                //$data = array_map("utf8_encode", $data); //added
                $num = count($data);
                /*echo "<p> $num fields in line $row: <br /></p>\n";
                for ($c=0; $c < $num; $c++) {
                    echo $data[$c] . "<br />\n";
                }
                */

                $name = $data[$columnidxvoornaam];
                $surname = empty($data[$columnidxtussenvoegsel]) ? $data[$columnidxachternaam] : $data[$columnidxtussenvoegsel] . ' ' . $data[$columnidxachternaam];

                if($columnidxlevel != -1){
                    $thislevel = $data[$columnidxlevel];
                }
                if($columnidxclass != -1){
                    $class = $data[$columnidxclass];
                }
                else {
                    $class = '';
                }
                if($columnidxcourse != -1){
                    $course = $data[$columnidxcourse];
                }
                else {
                    $course = '';
                }

                $tostore = [ 
                    'name' => $name,
                    'surname' => $surname,
                    'email' => strtolower($data[$columnidxemail]),
                    'level' => $thislevel,
                    'year' => $yearToLicence,
                    'class' => $class,
                    'course' => $course
                ];

                if(!empty($tostore['email'])){
                    $output[$row] = $tostore;
                    $row++;
                }
                else {
                    $skipped[] = $tostore;
                }
            }
            fclose($handle);
        }

        if(empty($output))
        {
            return [
                'valid' => false,
                'message' => 'Dit bestand heeft geen geldige entries.'
            ];
        }

        // check how many students
        if($CONFIRMATION == -1)
        {

            $tempoutput = '';

            // loop over the output to show it
            foreach($output as $entry)
            {
                $tempoutput .= $entry['name'] . ' ' . $entry['surname'] . ' (' . $entry['email'] . ')<br />';
            }

            foreach($skipped as $entry)
            {
                $tempoutput .= '<br />Skip: ' . $entry['name'] . ' ' . $entry['surname'];
            }

            return [
                'valid' => false,
                'showElement' => [
                    '#import-button-confirm-school-' . $school->id
                ],
                'message-info' => $tempoutput
            ];
        }

        // loop over all the entries, two options:
        // A: There is already an account with this e-mail --> couple the school automatically via a license code and couple the licenses of interest. Send the user an email with the info that the school is coupled and licenses are activated.
        // B: There is not an account yet --> create an account, couple the school and relevant licenses, and send the user a mail with a link to reset their password.
        foreach($output as $entry)
        {

            // check the mail
            $existinguser = Users::where('email', $entry['email'])->first();
            $sendmail = true;

            // create an account if the user does not exist yet
            if(!$existinguser)
            {
                $u = new Users();
                $u->name = $entry['name'];
                $u->surname = $entry['surname'];
                $u->email = $entry['email'];
                $u->is_activated = true;

                $u->generatePassword();

                $u->save();

                $u->setUserSetting('level', $entry['level']);

                // get the link
                $code = implode('!', [$u->id, $u->getResetPasswordCode()]);
                $link = 'https://www.examify.nl/wachtwoord-vergeten/' .  $code;

                $data = [
                    'name' => $u->name,
                    'schoolname' => $school->name,
                    'username' => $u->username,
                    'email' => $u->email,
                    'link' => $link,
                    'code' => $code
                ];

                // send a mail that this account has been created
                if($sendmail)
                {
                    Mail::queue('examify.exams::mail.account_generated_teacher', $data, function($message) use ($u) {
                        $message->to($u->email, $u->name . ' ' . $u->surname);
                        //$message->bcc('accounts@examify.nl', 'Account Registration');
                    }, 'mail');

                    // set that this mail has been sent
                    $u->setUserSetting('hasmailsent', 1);
                }
            }
            else {
                $u = $existinguser;
            }

            // make this user student for this school
            $u->makeTeacherForSchool($school->id, $entry['year'], true, false);

            // in case the classname is given, the level and the course, also add this teacher as a teacher.
            if(!empty($entry['class']) && !empty($entry['level']) && !empty($entry['course'])){

                // find the class for this course
                $thiscourse = Courses::where('level', strtolower($entry['level']))
                                    ->where('name', ucfirst($entry['course']))
                                    ->first();

                if(!empty($thiscourse))
                {
                    $thisclass = Classes::where('school_id', $school->id)
                                        ->where('schoolyear', $entry['year'])
                                        ->where('course_id', $thiscourse->id)
                                        ->where('name', $entry['class'])
                                        ->get();

                    if($thisclass->count() > 1){
                        return [
                            'valid' => false,
                            'message' => 'Er zijn meerdere klassen gevonden voor deze input.'
                        ];
                    }
                    if($thisclass->count() == 1){
                        $thisclass = $thisclass->first();
                        $thisclass->teachers()->syncWithoutDetaching([$u->id => ['is_teacher' => true ]]);
                    }
                }

            }


        }

        // check the file import
        return [
            'valid' => false,
            'data' => $output
        ];

    }

    // delete an licence
    function onDelete()
    {

        // get the licence
        $extraData = input('extraData', false);

        if(!$extraData)
        {
            return [
                'valid' => false
            ];
        }

        $extraData = json_decode($extraData, true);

        $key = $extraData['key'];

        // get the licence with this key
        $myLic = Licences::where('key', $key)->first();

        if(!$myLic->count()){
            return [
                'valid' => false,
                'message' => 'Deze licentie is al verwijderd.',
                'key' => $key
            ];
        }

        if($myLic->activated){
            return [
                'valid' => false,
                'message' => 'Deze licentie is geactiveerd. Geactiveerde licenties kunnen niet worden verwijderd.'
            ]; 
        }

        // get the associated school id
        $schoolid = $myLic->school_id;

        // validate that the user is admin of this school
        $user = Users::getUser();

        // validate the user is authorized to delete this one
        if(!$user || !$user->isAdminForSchool($schoolid)){
            return [
                'valid' => false,
                'message' => 'Je bent niet meer ingelogd of hebt geen rechten meer voor deze school.',
            ];
        }

        $myLic->delete();

        // get the school
        $school = Schools::find($schoolid);

        // update the list of teachers
        return [
            'valid' => true,
            'updateElement' => [
                '#list-of-teachers-' . $school->id => $this->renderPartial('examifyHelpers/portal/listOfTeachers', [ 'teachers' => $school->getTeachers($this->param('year')), 
                    'inactiveLics' => $school->getInactiveLicences($this->param('year')),
                    'school_id' => $school->id,
                    'year' => $this->param('year'),
                    'deleted_action' => true ])
            ]
        ];
    }

    function onRemoveTeacher(){
        // get the teacher id and school id
        $teacherid = input('teacher_id');
        $schoolid = input('school_id');

        // validate that the user is admin of this school
        $user = Users::getUser();

        if(!$user || !$user->isAdminForSchool($schoolid)){
            return [
                'valid' => false,
                'message' => 'Je bent niet meer ingelogd of hebt geen rechten meer voor deze school.',
            ];
        }

        $school = Schools::find($schoolid);

        // validate the teacher is already a teacher for this school
        $teacher = Users::find($teacherid);

        if(!$teacher || !$school || !$teacher->isTeacherForSchool($schoolid, $this->param('year'))){
            return [
                'valid' => false,
                'message' => 'Dit is geen docent meer binnen deze school.',
            ];
        }

        // first make sure it is not anymore admin
        $teacher->makeSchoolAdminForSchool($schoolid, false);
        $teacher->schools()->wherePivot('school_id', $schoolid)->wherePivot('schoolyear', $this->param('year'))->detach();

        // remove the licenses
        $lics = Licences::where('is_teacher', true)
                        ->where('user_id', $teacher->id)
                        ->where('schoolyear', $this->param('year'));

        foreach($lics as $lic)
        {
            $lic->activated = false;
            $lic->save();
        }

        // remove the 

        return [
            'valid' => true,
            'updateElement' => [
                '#list-of-teachers-' . $school->id => $this->renderPartial('examifyHelpers/portal/listOfTeachers', [ 'teachers' => $school->getTeachers($this->param('year')), 
                    'inactiveLics' => $school->getInactiveLicences($this->param('year')),
                    'school_id' => $school->id,
                    'year' => $this->param('year'),
                    'iscurrentyear' => $this->param('year') == Classes::getCurrentYear(),
                    'admin_changes' => true ])
            ]
        ];

    }

    function onRemoveAdmin($admin = false)
    {
        return $this->onMakeAdmin(false);
    }

    function onMakeAdmin($admin = true)
    {

        // get the teacher id and school id
        $teacherid = input('teacher_id');
        $schoolid = input('school_id');

        // validate that the user is admin of this school
        $user = Users::getUser();

        if(!$user || !$user->isAdminForSchool($schoolid)){
            return [
                'valid' => false,
                'message' => 'Je bent niet meer ingelogd of hebt geen rechten meer voor deze school.',
            ];
        }

        $school = Schools::find($schoolid);

        // validate the teacher is already a teacher for this school
        $teacher = Users::find($teacherid);

        if(!$teacher || !$school || !$teacher->isTeacherForSchool($schoolid, $this->param('year'))){
            return [
                'valid' => false,
                'message' => 'Dit is geen docent meer binnen deze school.',
            ];
        }

        // make it a school admin
        $teacher->makeSchoolAdminForSchool($schoolid, $admin);

        return [
            'valid' => true,
            'updateElement' => [
                '#list-of-teachers-' . $school->id => $this->renderPartial('examifyHelpers/portal/listOfTeachers', [ 'teachers' => $school->getTeachers($this->param('year')), 
                    'inactiveLics' => $school->getInactiveLicences($this->param('year')),
                    'school_id' => $school->id,
                    'year' => $this->param('year'),
                    'iscurrentyear' => $this->param('year') == Classes::getCurrentYear(),
                    'admin_changes' => true ])
            ]
        ];

    }

    // generate the licences
    function onGenerate()
    {

        // get the school_id
        $schoolid = input('school_id');

        // validate that the user is admin of this school
        $user = Users::getUser();

        if(!$user || !$user->isAdminForSchool($schoolid)){
            return [
                'valid' => false,
                'message' => 'Je bent niet meer ingelogd of hebt geen rechten meer voor deze school.',
            ];
        }

        // get the school
        $school = Schools::find($schoolid);

        // get the number of licences
        $nLics = intval(input('nlicences'));

        if($nLics < 1 || $nLics > 10){
            return [
                'valid' => false,
                'message' => 'Genereer minimaal 1 en maximaal 10 licenties.'
            ];
        }

        // add the licences
        for($ii = 0; $ii < $nLics; $ii++)
        {
            $newLic = new Licences();

            // set the properties
            $newLic->is_teacher = true;
            $newLic->school_id = $schoolid;
            $newLic->schoolyear = $this->param('year');
            $newLic->generateKey();

            // save it
            $newLic->save();
        }

        // update the list of teachers
        return [
            'valid' => true,
            'updateElement' => [
                '#list-of-teachers-' . $school->id => $this->renderPartial('examifyHelpers/portal/listOfTeachers', [ 'teachers' => $school->getTeachers($this->param('year')), 
                    'inactiveLics' => $school->getInactiveLicences($this->param('year')),
                    'school_id' => $school->id,
                    'year' => $this->param('year') ])
            ]
        ];

        

        // 

        // generate the licences


        // get the school id
        return [
            'valid' => true,
            'test' => $this->property('school_id')
        ];

    }
}
