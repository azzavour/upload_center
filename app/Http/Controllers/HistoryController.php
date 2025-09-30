<?php

namespace App\Http\Controllers;

use App\Services\UploadService;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    protected $uploadService;

    public function __construct(UploadService $uploadService)
    {
        $this->middleware('auth');
        $this->uploadService = $uploadService;
    }

    public function index()
    {
        $user = auth()->user();
        
        // Admin bisa lihat semua, user hanya lihat department sendiri
        $departmentId = $user->isAdmin() ? null : $user->department_id;
        
        $histories = $this->uploadService->getUploadHistory($departmentId);
        return view('history.index', compact('histories'));
    }

    public function show($id)
    {
        $user = auth()->user();
        $departmentId = $user->isAdmin() ? null : $user->department_id;
        
        $history = $this->uploadService->getUploadById($id, $departmentId);
        return view('history.show', compact('history'));
    }
}