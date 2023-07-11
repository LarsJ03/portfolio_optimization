<?php namespace Examify\Exams\Components;

use Cms\Classes\ComponentBase;
use Carbon\Carbon;
use \Examify\Exams\Models\Vouchers as Vouchers;

class AdminVouchers extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'AdminVouchers Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRender()
    {

        // get the list of vouchers
        $this->page['list_of_vouchers'] = Vouchers::all();

    }

    public function onEditVoucher()
    {
        $id = input('idnr', []);

        if(empty($id))
        {
            return [
                'valid' => false,
                'message' => 'Er is geen voucher geselecteerd.'
            ];
        }

        // edit the voucher
        return $this->onAddVoucher($id);

    }

    public function onAddVoucher($id = [])
    {

        // check if all the content is there
        $start_date = input('start_date', []);
        $start_time = input('start_time', []);
        $end_date   = input('end_date', []);
        $end_time   = input('end_time', []);
        $code       = input('code', []);
        $name       = input('name', []);
        $discount_perc = input('discount_perc', []);
        $discount_eur  = input('discount_eur', []);
        $limit         = input('limit', []);

        if(empty($start_date))
        {
            return [
                'valid' => false,
                'message' => 'Vul een startdatum in.'
            ];
        }

        if(empty($start_time))
        {
            return [
                'valid' => false,
                'message' => 'Vul een starttijd in.'
            ];
        }

        if(empty($end_date))
        {
            return [
                'valid' => false,
                'message' => 'Vul een einddatum in.'
            ];
        }

        if(empty($end_time))
        {
            return [
                'valid' => false,
                'message' => 'Vul een eindtijd in.'
            ];
        }

        // create the start time and end times
        $datetime_start = Carbon::createFromFormat('d-m-Y H:i', $start_date . ' ' . $start_time);
        $datetime_end = Carbon::createFromFormat('d-m-Y H:i', $end_date . ' ' . $end_time);

        // check that end is later than start
        if($datetime_end < $datetime_start)
        {
            return [
                'valid' => false,
                'message' => 'De eind datum/tijd moet na de start datum/tijd liggen.'
            ];
        }

        if(empty($name))
        {
            return [
                'valid' => false,
                'message' => 'Vul een naam in zodat je later kunt herkennen waarvoor deze code was.'
            ];
        }

        if(empty($code))
        {
            return [
                'valid' => false,
                'message' => 'Vul een code in.'
            ];
        }

        if( (empty($discount_perc) && empty($discount_eur)) || (!empty($discount_perc) && !empty($discount_eur)))
        {
            return [
                'valid' => false,
                'message' => 'Geef of een percentage of een bedrag aan korting op.'
            ];
        }

        $limit = intval($limit);

        if(empty($limit) || $limit < 1)
        {
            return [
                'valid' => false,
                'message' => 'Geef een geldig limiet op (>0).'
            ];
        }

        // in case the id is not empty, edit the voucher
        if(!empty($id))
        {
            $myVoucher = Vouchers::find($id);

            if(!$myVoucher)
            {
                return [
                    'valid' => false,
                    'message' => 'Dit is geen geldige voucher.'
                ];
            }

            // validate that the code is not in another voucher
            $othervoucherwithcode = Vouchers::where('code', $code)->where('id', '!=', $id)->count();
        }
        else {
            $myVoucher = new Vouchers();
            $myVoucher->count = 0;

            $othervoucherwithcode = Vouchers::where('code', $code)->count();
        }

        if($othervoucherwithcode > 0)
        {
            return [
                'valid' => false,
                'message' => 'Deze code bestaat al. Het moet een unieke code zijn.'
            ];
        }

        $myVoucher->limit = $limit;
        $myVoucher->start_time = $datetime_start->toDateTimeString();
        $myVoucher->end_time = $datetime_end->toDateTimeString();
        $myVoucher->code = $code;
        $myVoucher->name = $name;
        $myVoucher->discount_eur = $discount_eur > 0 ? $discount_eur : 0;
        $myVoucher->discount_perc = $discount_perc > 0 ? $discount_perc : 0;
        $myVoucher->save();

        // get the list of vouchers
        $list = Vouchers::orderByDesc('end_time')->get();

        // render it
        if(empty($id))
        {
            return [
                'valid' => true,
                'updateElement' => [
                    '#list-of-vouchers' => $this->renderPartial('examifyHelpers/portal/listOfVouchers', [
                        'list_of_vouchers' => $list
                    ])
                ]
            ];
        }
        else {
            return [
                'valid' => true,
                'message-info' => 'Wijzigingen opgeslagen!'
            ];
        }

    }
}
