<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use App\Models\SessionModel;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;

class UserController extends Controller
{
    public function index($id)
    {
        $session = SessionModel::where("sessionId", $id)->first();
        if (!$session) {
            return array(
                "success" => false,
                "msg" => "Wrong URL"
            );
        } else if ($session->status == 0) {
            $session->status = 1;
            $session->touch();
            $session->update();
        } else if ($session->status !== 1) {
            $payment = Payment::where("session_id", $session->id)->first();
            if ($payment)
                return view("payment", ["link" => $payment->simulate]);
            else
                return view("payment", ["link" => null]);
        }
        return view("upload");
    }
    public function upload(Request $request, $id)
    {
        $request->validate([
            'file' => 'required|mimes:docx,doc,pdf',
        ]);
        $name2 = explode(".", $request->file->getClientOriginalName());
        $file = $request->file('file');
        $file->move(base_path('public/uploads/' . $id), $id . "." . end($name2));
        if (end($name2) !== "pdf") {
            exec("doc2pdf ../public/uploads/" . $id . "/" . $id . "." . end($name2) . " /home/bengsky/S4/ePrint/public/document.pdf");
            unlink("../public/uploads/$id/" . $id . "." . end($name2));
            $name2[count($name2) - 1] = "pdf";
            exec("mkdir ../public/uploads/$id/photo");
            exec('gs -dNOPAUSE -dBATCH -sDEVICE=png16m -sOutputFile="../public/uploads/' . $id . '/photo/Pic-%d.png" "../public/uploads/' . $id . '/' . $id . '.pdf"');
        } else {
            exec("doc2pdf ../public/uploads/" . $id . "/" . $id . ".pdf/home/bengsky/S4/ePrint/public/document.pdf");
            exec("mkdir ../public/uploads/$id/photo");
            exec('gs -dNOPAUSE -dBATCH -sDEVICE=png16m -sOutputFile="../public/uploads/' . $id . '/photo/Pic-%d.png" "../public/uploads/' . $id . '/' . $id . '.pdf"');
        }
        $check = SessionModel::where("sessionId", $id)->first();
        if ($check) {
            $check->status = 2;
            $check->touch();
            $check->update();
        }
        return redirect("/" . $id);
    }
}