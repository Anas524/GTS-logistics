<?php

namespace App\Http\Controllers;

use App\Services\FedexTrackingService;
use Illuminate\Http\Request;

class FedexTrackingController extends Controller
{
    // Public page – no auth middleware needed
    public function showForm()
    {
        return view('gts.fedex-track');
    }

    public function track(Request $request, FedexTrackingService $fedex)
    {
        $request->validate([
            'tracking_number' => ['required', 'string', 'max:50'],
        ]);

        $trackingNumber = trim($request->input('tracking_number'));

        try {
            $data = $fedex->track($trackingNumber);

            return view('gts.fedex-track', [
                'tracking_number' => $trackingNumber,
                'result'          => $data,
            ]);

        } catch (\Throwable $e) {
            logger()->error('FedEx tracking failed', [
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->withErrors([
                    'tracking_number' => 'Could not fetch tracking details. Please check the number or try again later.',
                ]);
        }
    }
}
