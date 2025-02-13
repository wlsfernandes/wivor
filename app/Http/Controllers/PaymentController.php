<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Module;
use Illuminate\Support\Facades\Auth;
use Exception;

class PaymentController extends Controller
{
    // Display a list of payments
    public function index()
    {
        $payments = Payment::with(['student.user', 'certification'])->get();
        return view('payments.index', compact('payments'));
    }

    public function show($id)
    {
        $payment = Payment::findOrFail($id);
        return view('payments.show', compact('payment'));
    }

}
