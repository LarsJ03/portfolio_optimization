<?php namespace Examify\Exams\Components;

use Cms\Classes\ComponentBase;
use \Examify\Exams\Models\PracticeSessions as PracticeSessions;
use \Examify\Exams\Models\Users as Users;
use Carbon\Carbon as Carbon;

class TimeLimit extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'TimeLimit Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return [
            'practice_session_id' => [
                'title'         => 'Practice Session ID',
                'description'   => 'Practice Session ID',
                'default'       => 0,
                'type'          => 'string',
                'validationPattern'     => '^[0-9]+$',
                'validationMessage'     => 'School ID should be a numeric value'
            ]
        ];
    }

    public function onRender()
    {
        $this->page['psid'] = $this->property('practice_session_id');
    }

    public function getPracticeSession()
    {
        $user = Users::getUser();

        if(!$user){
            return;
        }

        $psid = input('psid', false);
        if(!$psid){ return; }

        // get the practice session
        return PracticeSessions::where('user_id', $user->id)->find($psid);
    }

    public function onStartSession()
    {

        // get the practice session
        $ps = $this->getPracticeSession();
        if(!$ps){
            return [
                'valid' => false,
                'message' => 'Je bent niet meer ingelogd of deze sessie is niet meer gevonden in ons systeem.',
            ];
        }

        // set to started if not yet done
        if(!$ps->started){
            $ps->started = true;
            $ps->start_time = Carbon::now();
            $ps->save();
        }

        // redirect
        return [
            'valid' => true,
            'redirect' => [
                'location' => url()->current()
            ]
        ];

    }
}
