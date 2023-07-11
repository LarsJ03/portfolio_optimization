<?php namespace Examify\Exams\Controllers;

use Illuminate\Routing\Controller;
use \Examify\Exams\Models\Orders as Orders;
use Illuminate\Http\Request;

/**
 * Mollie Webhook Controller
 */
class MollieWebhookController extends Controller {

    public function handle(Request $request) {
        if (! $request->has('id')) {
            return;
        }

        $mollieorder = mollie()->orders()->get($request->id);

        // find this ordernumber 
        $order = Orders::where('name', $mollieorder->orderNumber)->get();

        // it should be exactly one
        if($order->count() != 1)
        {
            return;
        }

        // get the first order
        $order = $order->first();

        // update the status 
        $order->status = $mollieorder->status;
        $order->save();

        // prevent that any open order of this person will be paid, since he can pay double then...
        if($mollieorder->isPaid())
        {

            // activate the licences
            $order->activateLicences();
            $order->generateInvoice();

        }
    }
}
