<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OrderController extends Controller
{
    // customer membuat order
    public function store(Request $request){
        $user = $this->authUser();

        if($user->role == 'driver')
            return response()->json(['message' => 'hanya penumpang yang dapat membuat order']);
        else{
            $order = \App\Order::create([
                'customer_id' => $user->id,
                'driver_id' => null,
                'fee' => $request->fee,
                'pickup_lat' => $request->pickup_lat,
                'pickup_long' => $request->pickup_long,
                'destination_lat' => $request->destination_lat,
                'destination_long' => $request->destination_long
            ]);
    
            return $order;
        }
    }


    // driver melihat daftar order yang tersedia
    public function available(){
        $user = $this->authUser();

        if($user->role == 'penumpang')
            return response()->json(['message' => 'hanya driver yang dapat melihat dafftar order yang tersedia']);
        else{
            $orders = \App\Order::select('*')->where('status', '=', 'available')->get();

            return $orders;
        }
    }


    // driver menerima order
    public function accept(Request $request){
        $user = $this->authUser();

        if($user->role == 'penumpang')
            return response()->json(['message' => 'hanya driver yang dapat menerima order']);
        else{
            $order_id = $request->order_id;
            $order = \App\Order::where('id', $order_id)->update([
                'driver_id' => $user->id,
                'status' => 'process'
            ]);

            if($order == 1)
                return response()->json(['message' => 'order berhasil diproses']);
            else
                return response()->json(['message' => 'order gagal diproses']);
        }
    }


    // pickup : order berhasil dilakukan
    // order status : process -> done
    public function pickup(Request $request){
        $user = $this->authUser();

        if($user->role == 'penumpang')
            return response()->json(['message' => 'hanya driver yang dapat melakukan pick up order']);
        else{
            $order_id = $request->order_id;
            $order = \App\Order::where('id', $order_id)->where('driver_id', $user->id)->update([
                'status' => 'done'
            ]);
            
            if($order == 1)
                return response()->json(['message' => 'order berhasil di pick up']);
            else
                return response()->json(['message' => 'order gagal di pick up']);
        }
    }


    // driver dashboard
    public function dashboard(){
        $user = $this->authUser();

        if($user->role == 'penumpang')
            return response()->json(['message' => 'dashboard hanya dapat diakses oleh driver']);
        else{
            $daily_income = \App\Order::selectRaw('SUM(fee) AS pendapatan_hari_ini')->where('driver_id', $user->id)->where('status', 'done')->whereRaw('created_at >= NOW() - INTERVAL 1 DAY')->first();
            $monthly_income = \App\Order::selectRaw('SUM(fee) AS pendapatan_bulan_ini')->where('driver_id', $user->id)->where('status', 'done')->whereRaw('created_at >= NOW() - INTERVAL 1 MONTH')->first();
            $daily_order = \App\Order::selectRaw('COUNT(id) AS pelanggan_hari_ini')->where('driver_id', $user->id)->where('status', 'done')->whereRaw('created_at >= NOW() - INTERVAL 1 DAY')->first();
            $dashboard = collect($daily_income)->merge($monthly_income)->merge($daily_order);

            return $dashboard;
        }
    }
}
