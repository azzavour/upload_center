<?php

namespace App\Http\Controllers;

use App\Services\UploadService;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    protected $uploadService;

    public function __construct(UploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }

    public function index()
    {
        $histories = $this->uploadService->getUploadHistory();
        return view('history.index', compact('histories'));
    }

    public function show($id)
    {
        $history = $this->uploadService->getUploadById($id);
        return view('history.show', compact('history'));
    }
}