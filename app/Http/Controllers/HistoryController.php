<?php

namespace App\Http\Controllers;

use App\Services\UploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $user = Auth::user();
        
        // Admin bisa lihat semua, user hanya lihat department sendiri
        $departmentId = $user->isAdmin() ? null : $user->department_id;
        
        $histories = $this->uploadService->getUploadHistory($departmentId);
        return view('history.index', compact('histories'));
    }

    public function show($id)
    {
        $user = Auth::user();
        $departmentId = $user->isAdmin() ? null : $user->department_id;
        
        $history = $this->uploadService->getUploadById($id, $departmentId);
        return view('history.show', compact('history'));
    }
}
