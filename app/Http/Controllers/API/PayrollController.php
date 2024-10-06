<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PayrollGroup;
use App\Models\PayrollRecipient;

class PayrollController extends Controller
{
    public function groups(){
        $payroll = PayrollGroup::where('user_id', auth()->user()->id)->paginate(50);
        return response()->json($payroll);
    }
    
    public function destroyGroup(Request $request){
        $request->validate([
          'id' => 'required'
        ]);
        $group = PayrollGroup::where('user_id', auth()->user()->id)->where('id', $request->id)->delete();
        
        return response()->json(['message' => 'Group deleted']);
    }
    
    public function updateGroup(Request $request){
        $request->validate([
          'id' => 'required'
        ]);
$group = PayrollGroup::where('user_id', auth()->id())->where('id', $request->id)->first();

if ($group) {
    $group->update(['status' => !$group->status]);
} else {
    // Handle the case where the group is not found
    return response()->json(['message' => 'Group not found'], 404);
}
        return response()->json(['message' => 'Group made Inactive']);
    }
    
    public function createGroup(Request $request){
        $request->validate([
           'name' => 'required|string'
        ]);
        
        $group = new PayrollGroup;
        $group->name = $request->name;
        $group->user_id = auth()->user()->id;
        $group->status = 1;
        $group->save();
        
        return response()->json(['message' => 'Payroll Group created']);
    }
    
    public function getGroup(Request $request){
        $request->validate([
            'id' => 'required'
        ]);
        $group = PayrollGroup::where('id', $request->id)->where('user_id', auth()->user()->id)->first();
        $recipients = PayrollRecipient::where('payroll_group_id', $request->id)->where('user_id', auth()->user()->id)->paginate(60);
        return response()->json([
            'group' => $group,
            'recipients' => $recipients
        ]);
    }
    public function recipient(Request $request){
        $request->validate([
            'payroll_group_id' => 'required|exists:payroll_groups,id',
            'wallet_address' => 'required',
            'name' => 'required',
            'amount' => 'required|numeric',
            'schedule' => 'required|in:daily,weekly,monthly,yearly',
        ]);
        
        PayrollRecipient::create([
          'payroll_group_id' => $request->payroll_group_id,
          'wallet_address' => $request->wallet_address,
          'name' => $request->name,
          'amount' => $request->amount,
          'sol' => $request->amount,
          'schedule' => $request->schedule,
          'status' => 1,
          'user_id' => auth()->user()->id
        ]);
        
        return response()->json(['message' => 'Recipient created']);
    }
    
public function destroyRecipient(Request $request)
{
    $request->validate([
        'id' => 'required|integer|exists:payroll_recipients,id',
    ]);

    try {
        PayrollRecipient::where('user_id', auth()->user()->id)
            ->where('id', $request->id)
            ->delete();
            
        return response()->json(['message' => 'Payroll recipient deleted successfully.'], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to delete payroll recipient.'], 500);
    }
}

}
