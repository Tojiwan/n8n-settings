<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Backpack\CRUD\app\Http\Controllers\CrudController;

class WorkflowTriggerController extends CrudController
{
    public function create()
    {
        return view('workflow-form');
    }

    public function submit(Request $request)
    {
        $data = $request->validate([
            'subject' => 'required|string',
            'message' => 'required|string',
        ]);

        Http::post('https://n8n.tigernethost.com/webhook-test/backpack-event', $data);

        \Prologue\Alerts\Facades\Alert::success('Workflow triggered successfully.')->flash();
        return redirect()->back();
    }
}