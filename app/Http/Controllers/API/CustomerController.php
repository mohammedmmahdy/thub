<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\Option;
use App\Models\Donation;
use App\Models\DriverRate;
use App\Models\CustomerRate;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\DonationPhoto;
use App\Models\TypeOfDonation;
use App\Helpers\HelperFunctionTrait;
use App\Http\Controllers\Controller;

class CustomerController extends Controller
{
    use HelperFunctionTrait;

    public function test()
    {
        return ('test home');
    }

    ##################################################################
    # Dashboard
    ##################################################################

    public function update_information(Request $request)
    {
        $user = auth('api')->user();
        $data = $request->validate([
            'name'              => 'required|string|max:191',
            'address'           => 'nullable|string|max:191',
            'housing_type'      => 'nullable|in:1,2',
            'state_id'          => 'nullable',
            'building_number'   => 'nullable',
            'floor_number'      => 'nullable',
            'apartment_number'  => 'nullable',
        ]);

        $user->userable()->update($data);
        $user->load('userable');

        return response()->json(compact('user'));
    }

    public function update_phone()
    {
        $user = auth('api')->user();

        $user->update(['verify_code' => $this->randomCode(4)]);

        return response()->json(['msg' => 'A confirmation code has been sent, check your inbox', 'code' => $user->verify_code]);
    }

    public function verify_phone(Request $request)
    {
        $request->validate([
            'phone'         => 'required|numeric|unique:users,phone',
            'verify_code'   => 'required|min:4|max:5'
        ]);

        $user = User::where(['phone' => auth()->user()->phone, 'verify_code'   => $request->verify_code])->first();

        if (empty($user)) {
            return response()->json(['msg' => 'Verify code is not correct'], 403);
        }

        $user->update(['phone' => $request->phone]);
        // $user->load('userable');

        return response()->json(['msg' => 'Your Phone Updated Successfuly']);
    }

    public function wallet()
    {
        $user = auth('api')->user();
        $balance = $user->balance;

        return response()->json(compact('balance'));
    }



    //------------------------- End Dashboard --------------------------//



    ##################################################################
    # Donation
    ##################################################################

    public function donate()
    {
        if (auth('api')->user()->type != 'customer') {
            return response()->json(['msg' => 'You Are Not Customer']);
        }
        $customer = auth('api')->user();
        dd($customer->userable);

        $data = request()->validate([
            'name'              => 'required|string|max:191',
            'address'           => 'required|string|max:191',
            'state_id'          => 'required|exists:states,id',
            'housing_type'      => 'required|in:1,2',
            'house_number'      => 'nullable|numeric',
            'building_number'   => 'nullable|numeric',
            'floor_number'      => 'nullable|numeric',
            'apartment_number'  => 'nullable|numeric',
            'pickup_date'       => 'required|date',
            'photos'            => 'nullable|array',
            'photos.*'          => 'nullable|image|mimes:png,jpg,jpeg',
            'donation_types'    => 'nullable|array',
            'donation_types.*'  => 'nullable|exists:donation_types,id',
        ]);

        $data['customer_id'] = $customer->id;

        if ($customer->donations->count() > 0) {
            $donation = Donation::create([
                'name'              => $data['name'],
                'state_id'          => $data['state_id'],
                'housing_type'      => $data['housing_type'],
                'house_number'      => $data['house_number'] ?? null,
                'building_number'   => $data['building_number'] ?? null,
                'floor_number'      => $data['floor_number'] ?? null,
                'apartment_number'  => $data['apartment_number'] ?? null,
                'customer_id'       => $data['customer_id'],
                'address'           => $data['address'],
                'pickup_date'       => $data['pickup_date'],
            ]);
        } else {
            $customer->update([
                'name'              => $data['name'],
                'state_id'          => $data['state_id'],
                'address'           => $data['address'],
                'housing_type'      => $data['housing_type'],
                'house_number'      => $data['house_number'] ?? null,
                'building_number'   => $data['building_number'] ?? null,
                'floor_number'      => $data['floor_number'] ?? null,
                'apartment_number'  => $data['apartment_number'] ?? null,
            ]);
            $donation = Donation::create([
                'name'              => $data['name'],
                'state_id'          => $data['state_id'],
                'address'           => $data['address'],
                'housing_type'      => $data['housing_type'],
                'house_number'      => $data['house_number'] ?? null,
                'building_number'   => $data['building_number'] ?? null,
                'floor_number'      => $data['floor_number'] ?? null,
                'apartment_number'  => $data['apartment_number'] ?? null,
                'customer_id'       => $data['customer_id'],
                'pickup_date'       => $data['pickup_date'],
            ]);
        }

        foreach ($data['photos'] as $photo) {
            DonationPhoto::create([
                'donation_id'   => $donation->id,
                'photo'         => $photo,
            ]);
        }
        foreach ($data['donation_types'] as $donation_type) {
            TypeOfDonation::create([
                'donation_id'           => $donation->id,
                'donation_type_id'      => $donation_type,
            ]);
        }

        $customer->load('donations.photos', 'donations.types.donationType');

        return response()->json($customer);
    }

    public function donations()
    {
        $data['donations'] = auth('api')->user()->userable->donations;
        return $data['donations']->load('photos');
    }

    //--------------------- End Donation -----------------------//



    ##################################################################
    # Notifications
    ##################################################################

    public function notifications()
    {
        $notifications = Notification::customer()->get();

        return response()->json($notifications, 200);
    }

    public function notification(Notification $notification)
    {
        return response()->json($notification, 200);
    }

    //--------------------- End Notifications -----------------------//


    ##################################################################
    # Rates
    ##################################################################

    public function rates($customerId)
    {
        $data['rates'] = CustomerRate::where('customer_id', $customerId)->get();
        $data['rates']->load('customer', 'driver');
        return response()->json($data);
    }

    public function rate(CustomerRate $customerRate)
    {
        $customerRate->load('customer', 'driver');
        return response()->json($customerRate);
    }

    public function addOrUpdateRate()
    {
        $validated = request()->validate([
            'driver_id'         => 'required',
            'rate'              => 'required',
            'report'            => 'required',
        ]);

        $validated['customer_id'] =  auth('api.customer')->id();

        $data['rate'] = DriverRate::updateOrCreate([
            'customer_id'   => auth('api.customer')->id(),
            'driver_id'     => request('driver_id')
        ], $validated);
        $data['rate']->load('customer', 'driver');
        return response()->json($data);
    }

    //--------------------- End Rates -----------------------//






}
