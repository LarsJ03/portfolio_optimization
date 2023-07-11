<?php namespace Examify\Exams\Components;

use Cms\Classes\ComponentBase;
use \Examify\Exams\Models\Schools as Schools;
use \Examify\Exams\Models\Users as Users;
use \Examify\Exams\Models\Courses as Courses;
use \Examify\Exams\Models\Classes as Classes;
use \Examify\Exams\Models\Licenses as Licences;
use Input;
use Mail;
use Str;

class AdminStudents extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'AdminStudents Component',
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

        if(!$school){
            return;
        }

        // check if the user is admin of this school
        // validate the user is logged in 
        $user = Users::getUser();

        if(!$user || !$user->isAdminForSchool($school->id))
        {
          return redirect('/login')->with('redirect-to', url()->current());
        }


        // set the school
        $this->page['school'] = $school;

        // get the teachers
        $this->page['students'] = $school->getStudents($this->param('year'));

        // also find the unactivated licences belonging to this school
        $this->page['inactiveLics'] = $school->getInactiveStudentLicences($this->param('year'));

        $this->page['school_id'] = $school->id;

        $this->page['year'] = $this->param('year');

        // get all the courses
        $this->page['courses'] = Courses::all();

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

        // get the classname
        $classname = input('classname', '');

        // get the courses
        $course_ids = input('course_ids', false);
        $courses_to_licence = [];

        if($course_ids)
        {
            $courses_to_licence = Courses::find($course_ids);
        }

        if(!empty($classname) && (empty($courses_to_licence) || $courses_to_licence->count() != 1))
        {
            return [
                'valid' => false,
                'message' => 'Er moet precies 1 vak geselecteerd zijn als de klasnaam gegeven is.'
            ];
        }

        $coupleclass = !empty($classname);
        $thislevel = '';
        $coursename = '';
        if($coupleclass)
        {
            // the level equals the course level
            $thislevel = $courses_to_licence->first()->level;
            $coursename = $courses_to_licence->first()->name;
        }

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
                    'index' => 0
                ],
                [
                    'column' => 'Tussenvoegsel',
                    'index' => 1,
                ],
                [
                    'column' => 'Achternaam',
                    'index' => 2
                ],
                [
                    'column' => 'E-mail',
                    'index' => 3
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
                ],
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

                if(empty($thislevel) && $num < 5)
                {
                    return [
                        'valid' => false,
                        'message' => 'Indien er geen niveau gegeven wordt moet het csv bestand in de kolom na de mail het niveau aangeven.'
                    ];
                }

                $name = $data[$columnidxvoornaam];
                $surname = empty($data[$columnidxtussenvoegsel]) ? $data[$columnidxachternaam] : $data[$columnidxtussenvoegsel] . ' ' . $data[$columnidxachternaam];
                $tostore = [ 
                    'name' => $name,
                    'surname' => $surname,
                    'email' => strtolower($data[$columnidxemail]),
                    'level' => !empty($thislevel) ? $thislevel : strtolower($data[$columnidxlevel]),
                    'year' => $yearToLicence
                ];

                $tostore['coursename'] = empty($coursename) ? $data[$columnidxcourse] : $coursename;
                $tostore['classname'] = $data[$columnidxclass];

                if(!empty($tostore['email']) && strpos($tostore['email'], '@') !== false){
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
                $tempoutput .= $entry['name'] . ' ' . $entry['surname'] . ' (' . $entry['email'] . ') ' . $entry['coursename'] . ' - ' . $entry['classname'] .  '<br />';
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
                    Mail::queue('examify.exams::mail.account_generated', $data, function($message) use ($u) {
                        $message->to($u->email, $u->name . ' ' . $u->surname);
                        //$message->bcc('accounts@examify.nl', 'Account Registration');
                    });

                    // set that this mail has been sent
                    $u->setUserSetting('hasmailsent', 1);
                }
            }
            else {
                $u = $existinguser;
            }

            // make this user student for this school
            $u->makeStudentForSchool($school->id, $entry['year'], true, $sendmail);

            // licence the courses
            if($courses_to_licence)
            {
                foreach($courses_to_licence as $course)
                {
                    // check if a similar license is already there
                    $checkLic = Licences::where('user_id', $u->id)
                                        ->where('activated', true)
                                        ->where('schoolyear', $entry['year'])
                                        ->where('course_id', $course->id)
                                        ->get();

                    // skip
                    if($checkLic->count() > 0)
                    {
                        continue;
                    }

                    $courseLic = new Licences();
                    $courseLic->generateKey();
                    $courseLic->course_id = $course->id;
                    $courseLic->user_id = $u->id;
                    $courseLic->activated = true;
                    $courseLic->school_id = $school->id;
                    $courseLic->schoolyear = $entry['year'];
                    $courseLic->save();
                }
            }

            // licence the courses for this class
            if(!empty($entry['coursename']) && !empty($entry['classname']))
            {
                // get the course
                $thiscourse = Courses::where('name', $entry['coursename'])->where('level', $entry['level'])->first();

                // get the course id for licences (this is for all levels)
                $allcourses = Courses::where('name', $entry['coursename'])->get();

                if($thiscourse)
                {
                    // get the class
                    $thisclass = Classes::where('course_id', $thiscourse->id)->where('school_id', $school->id)->where('name', $entry['classname'])->where('schoolyear', $entry['year'])->first();

                    // create it if it is not defined
                    if(!$thisclass)
                    {
                        $thisclass = new Classes();
                        $thisclass->school_id = $school->id;
                        $thisclass->course_id = $thiscourse->id;
                        $thisclass->name = $entry['classname'];
                        $thisclass->schoolyear = $entry['year'];
                        $thisclass->save();
                    }

                    // add the student to this class
                    $thisclass->students()->syncWithoutDetaching([$u->id => ['is_teacher' => false]]);

                    // create a licence for the corresponnding courses and levels
                    foreach($allcourses as $tempcourse)
                    {
                        $checkLic = Licences::where('user_id', $u->id)
                                        ->where('activated', true)
                                        ->where('schoolyear', $entry['year'])
                                        ->where('course_id', $tempcourse->id)
                                        ->get();

                        // skip
                        if($checkLic->count() > 0)
                        {
                            continue;
                        }

                        $courseLic = new Licences();
                        $courseLic->generateKey();
                        $courseLic->course_id = $tempcourse->id;
                        $courseLic->user_id = $u->id;
                        $courseLic->activated = true;
                        $courseLic->school_id = $school->id;
                        $courseLic->schoolyear = $entry['year'];
                        $courseLic->save();
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
                '#list-of-students-' . $school->id => $this->renderPartial('examifyHelpers/portal/listOfStudents', [ 'students' => $school->getStudents($this->param('year')), 
                    'inactiveLics' => $school->getInactiveStudentLicences($this->param('year')),
                    'school_id' => $school->id,
                    'year' => $this->param('year'),
                    'deleted_action' => true ])
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

        if($nLics < 1 || $nLics > 1000){
            return [
                'valid' => false,
                'message' => 'Genereer minimaal 1 en maximaal 1000 licenties.'
            ];
        }

        // add the licences
        for($ii = 0; $ii < $nLics; $ii++)
        {
            $newLic = new Licences();

            // set the properties
            $newLic->is_teacher = false;
            $newLic->school_id = $schoolid;
            $newLic->generateKey();
            $newLic->schoolyear = $this->param('year');

            // save it
            $newLic->save();
        }

        // update the list of teachers
        return [
            'valid' => true,
            'updateElement' => [
                '#list-of-students-' . $school->id => $this->renderPartial('examifyHelpers/portal/listOfStudents', [ 'students' => $school->getStudents($this->param('year')), 
                    'inactiveLics' => $school->getInactiveStudentLicences($this->param('year')),
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
