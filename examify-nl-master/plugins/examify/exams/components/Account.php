<?php namespace Examify\Exams\Components;

use Validator;
use Flash;
use Lang;

use Cms\Classes\ComponentBase;
use \RainLab\User\Components\Account as RainLabAccount;
use \RainLab\User\Models\User as User;
use \Examify\Exams\Models\Users as Users;
use \Examify\Exams\Models\Licenses as Licenses;
use \Examify\Exams\Models\UsersSubscriptions as UsersSubscriptions;
use Auth;
use Redirect;
use Event;
use RainLab\User\Models\Settings as UserSettings;
use ValidationException;
use ApplicationException;
use Exception;

class Account extends RainLabAccount
{
    public function componentDetails()
    {
        return [
            'name'        => 'Account Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    function onRun(){

        // run the parent onRun function from the Rainlab
        parent::onRun();

    }

    public function forTest()
    {
        throw new AuthException('The password attribute is required.');
    }

    public function onUpdate()
    {
        // setup the results array
        $myresultsarray = [];

        // in case it is not all valid, return the validateUpdate 
        $myvalidation = Users::validateUpdate();

        if(!$myvalidation['allvalid']){
            return $myvalidation;
        }

        $user = Users::getUser();

        // check which fields are updated
        if($user->email != post('email')){
            $myresultsarray['email'] = [
                'valid' => true,
                'message' => 'E-mail is gewijzigd!'
            ];
        }

        if($user->name != post('name')){
            $myresultsarray['name'] = [
                'valid' => true,
                'message' => 'Naam is gewijzigd!'
            ];
        }


        if($user->surname != post('surname')){
            $myresultsarray['surname'] = [
                'valid' => true,
                'message' => 'Achternaam is gewijzigd!'
            ];
        }

        if(!empty(post('password'))){
            $myresultsarray['password'] = [
                'valid' => true,
                'message' => 'Wachtwoord is gewijzigd!'
            ];
        }

        // fill the user
        $user->fill(post());

        // save the user
        $user->save();

        // reauthenticate the user if password has changed
        if (strlen(post('password'))) {
            Auth::login($user->reload(), true);
        }

        Flash::success(post('flash', Lang::get(/*Settings successfully saved!*/'rainlab.user::lang.account.success_saved')));

        return [
            'allvalid' => true,
            'inputvalidation' => $myresultsarray,
            'update_form' => true
        ];

    }

    public function onLicense()
    {

        // validate the login
        $myUser = Users::getUser();

        if(!$myUser){
            return [
                'allvalid' => false,
                'inputvalidation' => [
                    'licence_key' => [
                        'valid' => false,
                        'message' => 'Je bent niet ingelogd. Log in.'
                    ]
                ]
            ];
        }

        $myKey = input('licence_key', false);

        if(!$myKey){
            return [
                'allvalid' => false,
                'inputvalidation' => [
                    'licence_key' => [
                        'valid' => false,
                        'message' => 'Voeg een licentiecode in'
                    ]
                ]
            ];
        }

        // check if the code is active
        $myLic = Licenses::where('key', $myKey)->get();

        // check if it is there
        if($myLic->count() == 0){
            return [
                'allvalid' => false,
                'inputvalidation' => [
                    'licence_key' => [
                        'valid' => false,
                        'message' => 'Deze licentiecode is niet bekend bij ons.'
                    ]
                ]
            ];
        }

        if($myLic->count() > 1){
            return [
                'allvalid' => false,
                'inputvalidation' => [
                    'licence_key' => [
                        'valid' => false,
                        'message' => 'Deze licentiecode is niet uniek. Neem contact op.'
                    ]
                ]
            ];
        }

        // get the first one
        $myLic = $myLic->first();

        // check if it is active
        if($myLic->activated == true){
            return [
                'allvalid' => false,
                'inputvalidation' => [
                    'licence_key' => [
                        'valid' => false,
                        'message' => 'Deze licentiecode is al eens geactiveerd. Een code kan maar 1 keer gebruikt worden.'
                    ]
                ]
            ];
        }

        // in case it is a teacher licence and the user is not yet coupled as a teacher to that school, specify it
        if($myLic->is_teacher)
        {

            // check if the user is already a student for this school
            if($myUser->isStudentForSchool($myLic->school_id, $myLic->schoolyear))
            {
                return [
                    'allvalid' => false,
                    'inputvalidation' => [
                        'licence_key' => [
                            'valid' => false,
                            'message' => 'Je bent al een leerling voor deze school voor dit schooljaar (' . $myLic->schoolyear . '), het is niet mogelijk om je ook als docent aan te melden.'
                        ]
                    ]
                ];
            }

            // get the school it corresponds to and check if the user is a teacher for this school
            if($myUser->isTeacherForSchool($myLic->school_id, $myLic->schoolyear)){
                return [
                    'allvalid' => false,
                    'inputvalidation' => [
                        'licence_key' => [
                            'valid' => false,
                            'message' => 'Je bent al een docent voor deze school voor dit schooljaar (' . $myLic->schoolyear . '), het is niet nodig nogmaals een docentenlicentie te activeren.'
                        ]
                    ]
                ];
            }

            // add this user to the pivot table
            $myUser->schools()->attach($myLic->school_id, ['is_teacher' => true, 'schoolyear' => $myLic->schoolyear]);

            // add the user to this Licence
            $myLic->user_id = $myUser->id;
            $myLic->activated = true;
            $myLic->save();

            return [
                'allvalid' => true,
                'inputvalidation' => [
                    'licence_key' => [
                        'valid' => true,
                        'message' => 'Licentie toegevoegd.'
                    ]
                ],
                'update_form' => true,
                'update' => [
                    '#licence-overview' => $this->renderPartial('userHelpers/licences.htm')
                ]
            ];




        }

        // in case it is a student licence and the user is not yet coupled as a student to that school, specify it
        if(!$myLic->is_teacher && $myLic->course_id == 0)
        {
            // get the school it corresponds to and check if the user is a student for this school
            if($myUser->isStudentForSchool($myLic->school_id, $myLic->schoolyear)){
                return [
                    'allvalid' => false,
                    'inputvalidation' => [
                        'licence_key' => [
                            'valid' => false,
                            'message' => 'Je bent al een leerling van deze school, het is niet nodig nogmaals een leerlinglicentie te activeren.'
                        ]
                    ]
                ];
            }

            if($myUser->isTeacherForSchool($myLic->school_id, $myLic->schoolyear)){
                return [
                    'allvalid' => false,
                    'inputvalidation' => [
                        'licence_key' => [
                            'valid' => false,
                            'message' => 'Je bent al een leraar van deze school. Je kunt niet ook nog een leerling koppeling aanmaken.'
                        ]
                    ]
                ];
            }

            // check if the user has already a school assigned
            $check = $myUser->schools()->wherePivot('is_teacher', false)->wherePivot('schoolyear', $myLic->schoolyear);
            if($check->count())
            {
                return [
                    'allvalid' => false,
                    'inputvalidation' => [
                        'licence_key' => [
                            'valid' => false,
                            'message' => 'Je bent al een leerling van een andere school voor dit schooljaar. Dit is niet mogelijk.'
                        ]
                    ]
                ];
            }

            // add this user to the pivot table
            $myUser->schools()->attach($myLic->school_id, ['is_teacher' => false, 'schoolyear' => $myLic->schoolyear]);

            // add the user to this Licence
            $myLic->user_id = $myUser->id;
            $myLic->activated = true;
            $myLic->save();

            return [
                'allvalid' => true,
                'inputvalidation' => [
                    'licence_key' => [
                        'valid' => true,
                        'message' => 'Licentie toegevoegd.'
                    ]
                ],
                'update_form' => true,
                'update' => [
                    '#licence-overview' => $this->renderPartial('userHelpers/licences.htm')
                ]
            ];




        }

        // check if this user already has this class activated
        $myLics = $myUser->licences;
        if($myLics->contains('course_id', $myLic->course_id)){
            return [
                'allvalid' => false,
                'inputvalidation' => [
                    'licence_key' => [
                        'valid' => false,
                        'message' => 'Je hebt al een soortgelijke licentie (zelfde vak). Het is niet nodig nog een licentie voor hetzelfde vak toe te voegen.'
                    ]
                ]
            ];
        }

        // add the user to this Licence
        $myLic->user_id = $myUser->id;
        $myLic->activated = true;
        $myLic->save();

        // add this course to the subscriptions of the user
        $mySub = new UsersSubscriptions();
        $mySub->course_id = $myLic->course_id;
        $mySub->user_id = $myUser->id;
        $mySub->save();

        return [
            'allvalid' => true,
            'inputvalidation' => [
                'licence_key' => [
                    'valid' => true,
                    'message' => 'Licentie toegevoegd.'
                ]
            ],
            'update_form' => true,
            'update' => [
                '#licence-overview' => $this->renderPartial('userHelpers/licences.htm')
            ]
        ];

    }

    public function onSignin()
    {

        $data = post();

        // check if login is there
        if (!array_key_exists('login', $data)) {
            $data['login'] = post('username', post('email'));
        }

        // validate
        // first validate all
        $myvalidation = Users::validateLogin();

        // return if not all valid
        if(!$myvalidation['allvalid']){
            return $myvalidation;
        }

        /*
         * Authenticate user
         */
        $credentials = [
            'login'    => array_get($data, 'login'),
            'password' => array_get($data, 'password')
        ];

        /*
        * Login remember mode
        */
        switch ($this->rememberLoginMode()) {
            case UserSettings::REMEMBER_ALWAYS:
                $remember = true;
                break;
            case UserSettings::REMEMBER_NEVER:
                $remember = false;
                break;
            case UserSettings::REMEMBER_ASK:
                $remember = (bool) array_get($data, 'remember', false);
                break;
        }

        Event::fire('rainlab.user.beforeAuthenticate', [$this, $credentials]);

        try {
            // always remember!
            $remember = true;
            $user = Auth::authenticate($credentials, $remember);
        }
        catch (Exception $e){

            // check if the exception says something about suspended, and then update the text
            if(strpos($e->getMessage(), 'suspended') !== false){
                $mymessage = 'Dit e-mail adres is tijdelijk geblokkeerd vanwege te veel foute inlog pogingen.';
            }
            else {
                $mymessage = 'Dit wachtwoord hoort niet bij dit e-mail adres. Probeer opnieuw.';
            }

            $myvalidation['allvalid'] = false;
            $myvalidation['inputvalidation']['password'] = [
                'valid' => false,
                'message' => $mymessage
            ];
            
            return $myvalidation;
        }

        if ($user->isBanned()) {
            Auth::logout();

            $myvalidation['allvalid'] = false;
            $myvalidation['inputvalidation']['password'] = [
                'valid' => false,
                'message' => 'Dit e-mail adres is permanent geblokkeerd vanwege te veel foute inlog pogingen.'
            ];
            
            return $myvalidation;
        }

        /*
         * Redirect
         */
        if ($redirect = $this->makeRedirection(true)) {

            $myvalidation['allvalid'] = true;
            $myvalidation['inputvalidation'] = [
                'email' => ['valid' => true ],
                'password' => [
                    'valid' => true,
                    'message' => 'Succesvol ingelogd! Veel plezier!'
                ]
            ];

            // check if the redirect location is there
            $redirect_location = input('redirect-to', '/oefenen');

            $myvalidation['update'] = false;
            $myvalidation['update_form'] = true;

            if($redirect_location){
                $myvalidation['redirect'] = [
                    'location' => $redirect_location,
                    'after' => 1000
                ]; 
            }
            
            return $myvalidation;

        }

    }


    public function onRegister()
    {
        // first validate all
        $myvalidation = Users::validateRegistration();

        // return if not all valid
        if(!$myvalidation['allvalid']){
            return $myvalidation;
        }

        // call the parent
        parent::onRegister();

        return [ 
            'allvalid' => 1,
            'update' => [
                '.container.exam-questions' => $this->renderPartial('userHelpers/registration-successful.htm')
            ],
            'call-js-function' => [
                'alignFooter' => false
            ]
        ];



    }
}
