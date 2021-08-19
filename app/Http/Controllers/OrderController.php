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
                'customer_name' => $user->name,
                'driver_id' => null,
                'fee' => $request->fee,
                'pickup_lat' => $request->pickup_lat,
                'pickup_long' => $request->pickup_long,
                'destination_lat' => $request->destination_lat,
                'destination_long' => $request->destination_long,
                'destination_address' =>$request->destination_address
            ]);
    
            return $order;
        }
    }


    // driver melihat daftar order yang tersedia
    public function check(){
        $user = $this->authUser();

        if($user->role == 'penumpang')
            return response()->json(['message' => 'hanya driver yang dapat melihat dafftar order yang tersedia']);
        else{
            $current_order = \App\Order::where('driver_id', '=', $user->id)->where('status', '=', 'process')->get()->count();

            // jika driver masih punya order yang masih berlangsung maka tampilkan hanya order yang sedang berlangsung
            if($current_order > 0){
                $order = \App\Order::where('driver_id', '=', $user->id)->where('status', '=', 'process')->first();
                return $order;
            }
            // jika tidak tampilkan semua order yang tersedia
            else{
                $orders = \App\Order::select('*')->where('status', '=', 'process')->where('driver_id', null)->get();
                
                return $orders;
            }
        }
    }


    // driver menerima order
    public function pickup(Request $request){
        $user = $this->authUser();

        // check role user
        if($user->role == 'penumpang')
            return response()->json(['message' => 'hanya driver yang dapat menerima order']);
        else{
            // cek apakah driver masih memiliki order yang belum diselesaikan
            $current_order = \App\Order::where('status', '=', 'process')->where('driver_id', $user->id)->count();

            if($current_order == 1){
                return response()->json(['message' => 'Anda masih memiliki order yang belum diselesaikan']);
            }
            else{
                // driver menerima pesanan
                $order_id = $request->order_id;
                $order = \App\Order::where('id', $order_id)->where('status', '=', 'process')->where('driver_id', null)->update([
                    'driver_id' => $user->id
                ]);
                $order_data = \App\Order::where('id', $order_id)->first();

                if($order == 1)
                    // return response()->json(['message' => 'order berhasil diproses']);
                    return $order_data;
                else
                    return response()->json(['message' => 'order gagal diproses']);
            }
        }
    }

    // cek order yang sedang berlangsung
    // role : driver
    public function current(){
        $user = $this->authUser();

        if($user->role == 'penumpang'){
            return response()->json(['message' => 'hanya driver yang dapat mengakses menu ini']);
        }
        else{
            $order = \App\Order::where('driver_id', '=', $user->id)->where('status', '=', 'process')->first();

            return $order;
        }
    }


    // pickup : order berhasil dilakukan
    // order status : process -> done
    public function done(Request $request){
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

    // customer membatalkan pesanan
    public function cancel(Request $request){
        $user = $this->authUser();
        $order_id = $request->order_id;

        // mengecek role user dan mengubah status order menjadi canceled
        if($user->role == 'penumpang'){
            $order = \App\Order::where('id', $order_id)->where('customer_id', $user->id)->where('status', '=', 'process')->update(['status' => 'canceled']);
        }
        elseif($user->role == 'driver'){
            $order = \App\Order::where('id', $order_id)->where('driver_id', $user->id)->where('status', '=', 'process')->update(['status' => 'canceled']);
        }
        else{
            return response()->json(['message' => 'role user tidak valid']);
        }
        
        // return value
        if($order == 1){
            return response()->json(['message' => 'order berhasil di cancel']);
        }
        else{
            return response()->json(['message' => 'order gagal di cancel']);
        }
    }

    // data driver yang menjemput pesanan
    public function driver(Request $request){
        $user = $this->authUser();

        if($user->role == 'driver'){
            return response()->json(['message' => 'menu ini hanya dapat diakses oleh penumpang']);
        }
        else{
            $driver_id = \App\Order::where('id', $request->order_id)->first()->driver_id;

            if($driver_id == null){
                return response()->json(['message' => 'belum ada driver yang menerima pesanan ini']);
            }
            else{
                $driver = \App\User::where('id', $driver_id)->first();

                return response()->json([
                    'nama' => $driver->name,
                    'nomor telfon' => $driver->phone,
                    'nomor kendaraan' => $driver->nomor_kendaraan 
                ]);
            }
        }
    }
}