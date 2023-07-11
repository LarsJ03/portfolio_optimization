<?php namespace Examify\Exams\Models;

use Model;
use \Examify\Exams\Models\Courses as Courses;
use \Examify\Exams\Models\Licenses as Licences;
use Renatio\DynamicPDF\Classes\PDF as PDF;
use \Examify\Exams\Models\Users as Users;
use Carbon\Carbon;
use \Examify\Exams\Models\Vouchers as Vouchers;

/**
 * Model
 */
class Orders extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'examify_exams_orders';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $jsonable = [
        'courses',
        'pricing',
        'lines',
    ];

    public $hasMany = [
        'licences' => ['Examify\Exams\Models\Licenses', 'key' => 'order_id']
    ];

    public function licences()
    {
        return $this->hasMany('Examify\Exams\Models\Licenses', 'order_id');
    }

    public function getCourses()
    {
        $temp = collect($this->courses);
        return $courses = Courses::find($temp->pluck('id'));
    }

    public function getSchoolYears()
    {
        $pricing = $this->pricing;
        $year = [];
        foreach($pricing['years'] as $year => $elem)
        {
            if($elem['active'])
            {
                $result[] = $year;
            }
        }
        return $result;
    }

    // get the status of the order
    public function getMollieStatus()
    {
        // in case the mollie_id = DISCOUNT, no transaction is done and status should be paid
        if($this->mollie_id == 'DISCOUNT'){
            return 'paid';
        }
        $order = mollie()->orders()->get($this->mollie_id);
        return $order->status;
    }

    public function getMollieObject()
    {
        return mollie()->orders()->get($this->mollie_id);
    }

    // activate licences
    public function activateLicences()
    {
        // only do this if the status is paid
        if($this->status == 'paid' && $this->activated != 1)
        {
            // loop over the licences
            $pricing = $this->pricing;

            // loop over the years
            foreach($pricing['years'] as $year => $elem)
            {
                $courseids = $elem['courseids'];

                // check if the user has already a licence for this
                $u = Users::find($this->user_id);

                foreach($courseids as $courseid)
                {
                    if($u->hasLicenceForCourseIdAndYear($courseid, $year)){
                        continue;
                    }

                    $courseLic = new Licences();
                    $courseLic->generateKey();
                    $courseLic->course_id = $courseid;
                    $courseLic->user_id = $u->id;
                    $courseLic->activated = true;
                    $courseLic->school_id = 0;
                    $courseLic->schoolyear = $year;
                    $courseLic->order_id = $this->id;
                    $courseLic->save();

                }

            }

            // update all the other orders of this user that are open, since they might not be up to date anymore
            $otherorders = Orders::where('user_id', $this->user_id)->where('activated', 0)->where('status', '!=', 'paid')->get();

            foreach($otherorders as $ordertocancel)
            {
                $ordertocancel->status = 'overruled';
                $ordertocancel->save();
            }

            if($this->voucher_id){
                $voucher = Vouchers::find($this->voucher_id);
                $voucher->count = $voucher->count + 1;
                $voucher->save();
            }

            // add counter to the voucher if defined, and set activated to true
            $this->activated = true;
            $this->save();

        }
    }

    public function generateInvoice()
    {
        if($this->getMollieStatus() != 'paid')
        {
            //exit('Error: not paid.');
        }

        $templateCode = 'examify.exams::pdf.invoice';

        $user = Users::find($this->user_id);
        $pricing = $this->pricing;

        // in case there is a voucher ID linked to it, add the voucher
        if($this->voucher_id){
            $voucher = Vouchers::find($this->voucher_id);
        }
        else {
            $voucher = false;
        }

        $data = [
            'name' => $this->name,
            'date' => Carbon::now()->format('d-m-Y'),
            'user' => $user,
            'pricing' => $pricing,
            'order' => $this,
            'voucher' => $voucher
        ];

        $filename = $this->generateFilename();
        
        PDF::loadTemplate($templateCode, $data)
            ->setOptions([
                'DOMPDF_ENABLE_CSS_FLOAT' => true,
                'isRemoteEnabled' => true
            ])
            ->save($filename['abs']);

        $this->invoice = $filename['rel'];
        $this->save();
    }

    public function getToken($length)
    {
         $token = "";
         $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
         $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
         $codeAlphabet.= "0123456789";
         $max = strlen($codeAlphabet); // edited

        for ($i=0; $i < $length; $i++) {
            $token .= $codeAlphabet[random_int(0, $max-1)];
        }

        return $token;
    }

    public function generateFilename()
    {
        $dirToken = 'invoices/' . $this->getToken(32);
        $dir = base_path($dirToken);

        while(file_exists($dir)){
            $dirToken = $this->getToken(32);
            $dir = base_path($dirToken);
        }

        mkdir($dir, 0777, true);

        $pdfname = 'invoice-' . $this->name . '.pdf';
        return [
            'abs' => $dir . '/' . $pdfname,
            'rel' => $dirToken . '/' . $pdfname
        ];
    }

}
