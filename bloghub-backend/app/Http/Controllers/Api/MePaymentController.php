<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MePaymentController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $payments = Payment::query()
            ->whereHas('subscription', fn ($q) => $q->where('user_id', request()->user()->id))
            ->with(['subscription.tier.creatorProfile'])
            ->orderByDesc('checkout_date')
            ->get();

        return PaymentResource::collection($payments);
    }
}
