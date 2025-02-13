<?php
namespace App\Http\Controllers;

use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Certification;
use App\Models\Student;
use App\Models\Payment;

use Illuminate\Support\Facades\Auth;

use Exception;

class PayPalController extends Controller
{
    /**
     * Create a PayPal order for payment.
     */

    public function createPayment($id)
    {

        $certification = Certification::where('institution_id', Auth::user()->institution_id)
            ->findOrFail($id);
        $amount = $certification->amount;

        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->setAccessToken($provider->getAccessToken());

        // Create an order with PayPal
        $response = $provider->createOrder([
            "intent" => "CAPTURE", // Indicates that payment will be captured immediately
            "purchase_units" => [
                [
                    "amount" => [
                        "currency_code" => "BRL", // Currency set to BRL
                        "value" => $amount, // The amount to charge
                    ]
                ]
            ],
            "application_context" => [
                "return_url" => route('paypal.capture', ['certification_id' => $id, 'amount' => $amount]),
                "cancel_url" => route('test.paypal')
            ]
        ]);

        // Redirect the user to PayPal to approve the payment
        if (isset($response['id']) && isset($response['links'])) {
            foreach ($response['links'] as $link) {
                if ($link['rel'] === 'approve') {
                    return redirect($link['href']);
                }
            }
        }

        // If something goes wrong, redirect back with an error
        return redirect()->back()->withErrors('Something went wrong while creating the payment.');
    }

    /**
     * Capture the payment after user approval.
     */
    public function capturePayment(Request $request)
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->setAccessToken($provider->getAccessToken());

        try {
            $response = $provider->capturePaymentOrder($request->query('token'));

            Log::info('PayPal Response:', $response);

            if (isset($response['status']) && $response['status'] === 'COMPLETED') {
                $transactionId = $response['id'] ?? 'unknown';
                $amount = $request->query('amount');
                $currency = 'BRL';
                $user = auth()->user();
                $student = $user->student;
                $studentId = $student->id;
                $certificationId = $request->query('certification_id');

                // Save payment data
                Payment::create([
                    'student_id' => $studentId,
                    'certification_id' => $certificationId,
                    'transaction_id' => $transactionId,
                    'status' => 'COMPLETED',
                    'amount' => $amount,
                    'currency' => $currency,
                ]);

                // Associate student with the discipline
                $student = Student::find($studentId);
                $student->certifications()->attach($certificationId, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return redirect()->route('certifications.listCertifications')->with('success', 'Payment successful. You now have access to this Certification.');
            }

            return redirect()->route('certifications.listCertifications')->withErrors('Payment failed. Please try again.');
        } catch (Exception $e) {
            return redirect()->route('certifications.listCertifications')->withErrors('An error occurred: ' . $e->getMessage());
        }
    }
}
