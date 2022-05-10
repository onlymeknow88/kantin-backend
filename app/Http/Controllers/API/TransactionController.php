<?php

namespace App\Http\Controllers\API;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\TransactionItem;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function all(Request $request)
    {
        $id = $request->input('id');
        $limit = $request->input('limit', 10);
        $status = $request->input('status');

        if($id)
        {
            $transaction = Transaction::with(['items.product'])->find($id);

            if($transaction)
                return ResponseFormatter::success(
                    $transaction,
                    'Data transaksi berhasil diambil'
                );
            else
                return ResponseFormatter::error(
                    null,
                    'Data transaksi tidak ada',
                    404
                );
        }

        $transaction = Transaction::with(['items.product'])->where('users_id', Auth::user()->id);

        if($status)
            $transaction->where('status', $status);

        return ResponseFormatter::success(
            $transaction->paginate($limit),
            'Data list transaksi berhasil diambil'
        );
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'exists:products,id',
            'total_price' => 'required',
            'sub_total_item' => 'required',
            'status' => 'required|in:PENDING,SUCCESS,CANCELLED,FAILED',
        ]);

        $transaction = Transaction::create([
            'users_id' => Auth::user()->id,
            'total_price' => $request->total_price,
            'sub_total_item' => $request->sub_total_item,
            'status' => $request->status
        ]);

        foreach ($request->items as $product) {
            TransactionItem::create([
                'users_id' => Auth::user()->id,
                'products_id' => $product['id'],
                'transactions_id' => $transaction->id,
                'quantity' => $product['quantity']
            ]);
        }

        return ResponseFormatter::success($transaction->load('items.product'), 'Transaksi berhasil');
    }

    public function cancelOrder(Request $request) {
        $request->validate([
            'status' => 'required|in:PENDING,SUCCESS,CANCELLED,FAILED',
        ]);

        $data['status'] = $request->status;

        $transaction = Transaction::find($request->id);
        $transaction->update($data);

        return ResponseFormatter::success($transaction, 'Cancel Berhasil');
    }

    public function confirmOrder(Request $request) {
        $request->validate([
            'status' => 'required|in:PENDING,SUCCESS,CANCELLED,FAILED',
        ]);

        $data['status'] = $request->status;

        $transaction = Transaction::find($request->id);
        $transaction->update($data);

        return ResponseFormatter::success($transaction, 'Cancel Berhasil');
    }

    public function getOrderByStatus(Request $request) {
        $request->validate([
            'status' => 'required|in:PENDING,SUCCESS,CANCELLED,FAILED',
        ]);

        $status = $request->status;

        if(Auth::user()->roles == 'ADMIN'){
            $transaction =  Transaction::with(['items.product'])->where('status',$status)->get();
            return ResponseFormatter::success($transaction, 'Get Transaction Status Berhasil');
        } 

        return ResponseFormatter::error(
            null,
            'Data transaksi tidak ada',
            404
        );


    }

    public function getHistoryOrder(Request $request) {
        
        $request->validate([
            'status_cancel' => 'required|in:PENDING,SUCCESS,CANCELLED,FAILED',
            'status_sucess' => 'required|in:PENDING,SUCCESS,CANCELLED,FAILED',
        ]);

        $s_cancel = $request->status_cancel;
        $s_success = $request->status_sucess;

        if(Auth::user()->roles == 'ADMIN') {
            $cancel =  Transaction::with(['items.product'])->where('status',$s_cancel)->get();
            $success = Transaction::with(['items.product'])->where('status',$s_success)->get();
        } elseif (Auth::user()->roles == 'USER') {
            $cancel =  Transaction::with(['items.product'])->where('users_id',Auth::user()->id)->where('status',$s_cancel)->get();
            $success = Transaction::with(['items.product'])->where('users_id',Auth::user()->id)->where('status',$s_success)->get();
        }
        
        $data = [
            'cancel' => $cancel,
            'success' => $success
        ];
        
        return ResponseFormatter::success($data, 'Get Transaction Status Berhasil');
    }

    public function detailItem(Request $request) {
        $id = $request->input('id');

            $items = Transaction::with(['items.product'])->where('id', $id)->first();

            if($items)
                return ResponseFormatter::success(
                    $items,
                    'Data transaksi berhasil diambil'
                );
            else
                return ResponseFormatter::error(
                    null,
                    'Data transaksi tidak ada',
                    404
                );

    }
}
