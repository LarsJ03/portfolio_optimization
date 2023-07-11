<?php namespace Examify\Exams\Controllers;

use Illuminate\Routing\Controller;
use Carbon\Carbon as Carbon;
use \Examify\Exams\Models\Users as Users;
use \Examify\Exams\Models\PracticeSessions as PracticeSessions;

/**
 * Stream Controller Back-end Controller
 */
class StreamController extends Controller
{
    /**
     * The stream source.
     *
     * @return \Illuminate\Http\Response
     */
    public function timerLeft($psid)
    {

        return response()->stream(function () use ($psid) {
            $user = Users::getUser();

            // get the practice session
            // get the psid
            //$psid = $this->param('psid');

            if(!$user)
            {
                echo "NO!";
                flush();
                return;
            }

            $time = date('r');
           //echo "data: The server time is: {$time} and the user is: {$user->id}, psid: {$psid}\n\n";

            $ps = PracticeSessions::where('user_id', $user->id)->find($psid);

            if(!$ps){ echo "data: NO!\n\n"; flush(); return; }

            if($ps->finished)
            {
                echo "data: 0 min.\n\n";
            }

            $nminleft = Carbon::now()->diffInMinutes($ps->start_time->addMinutes($ps->time_limit_mins), false);

            $nsecleft = Carbon::now()->diffInSeconds($ps->start_time->addMinutes($ps->time_limit_mins), false);

            // compensate for rounding in minutes
            if($nminleft == 0 && $nsecleft < 0)
            {
                $nminleft = -1;
            }

            echo "data: ". $nminleft . "\n\n";
            
            flush();
        }, 200, [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'text/event-stream',
        ]);
    }
}
