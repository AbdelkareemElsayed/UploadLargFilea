<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;

class FileUploadController extends Controller {

    /**
     * @return Application|Factory|View
     */
    public function index() {
        return view('index');
    }

    public function uploadLargeFiles(Request $request) {
        $receiver = new FileReceiver('file', $request, HandlerFactory::classFromRequest($request));

        if (!$receiver->isUploaded()) {
            dd('error');
        }

        $fileReceived = $receiver->receive(); // receive file
       try{ if ($fileReceived->isFinished()) { // file uploading is complete / all chunks are uploaded
            $file = $fileReceived->getFile(); // get file
            $filename = $file->getClientOriginalName();
            $data = $file->storeAs('test',$filename,'s3');

             //   $url = Storage::disk('s3')->url($data);
              dd($data);

        }}catch(Exception $e){
            Log::error($e->getMessage());
        }

        // otherwise return percentage informatoin
        $handler = $fileReceived->handler();
        return [
            'done' => $handler->getPercentageDone(),
            'status' => true
        ];
    }


    public function create()
    {
        return view('upload');
    }


    public function store(Request $request)
    {

        $this->validate($request, [
            'attachment' => 'required|file|mimes:png,jpg,png'
        ]);

        try {

             $file = $request->file('attachment');
             $filename = $file->getClientOriginalName();
             $data = $file->storeAs('test',$filename,'s3');
             $url = Storage::disk('s3')->url($data);
             return redirect($url);

         } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }

}
