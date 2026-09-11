<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectFile;
use App\Services\CurrentUserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProjectFileController extends Controller
{
    public function __construct(
        protected CurrentUserService $currentUserService
    ) {}

    public function store(Request $request, Project $project)
    {
        $org = $this->currentUserService->getOrganization();
        $user = $this->currentUserService->getUser();

        if ($project->organization_id !== $org->id) {
            abort(404);
        }

        $request->validate([
            'file' => 'required|file|max:20480', // 20MB limit
        ]);

        $uploadedFile = $request->file('file');
        $originalName = $uploadedFile->getClientOriginalName();
        $fileSize = $uploadedFile->getSize();
        $extension = strtolower($uploadedFile->getClientOriginalExtension());

        // Categorize file type
        $fileType = 'other';
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'])) {
            $fileType = 'image';
        } elseif (in_array($extension, ['pdf', 'doc', 'docx', 'txt', 'rtf'])) {
            $fileType = 'document';
        } elseif (in_array($extension, ['zip', 'rar', '7z', 'tar', 'gz'])) {
            $fileType = 'archive';
        } elseif (in_array($extension, ['js', 'php', 'py', 'html', 'css', 'json', 'sql'])) {
            $fileType = 'code';
        }

        $path = $uploadedFile->store("projects/{$project->id}/files", 'public');

        ProjectFile::create([
            'organization_id' => $org->id,
            'project_id' => $project->id,
            'user_id' => $user->id,
            'name' => $originalName,
            'file_path' => $path,
            'file_type' => $fileType,
            'file_size' => $fileSize,
        ]);

        return redirect()->back()->with('success', 'File uploaded successfully.');
    }

    public function destroy(Project $project, ProjectFile $file)
    {
        $org = $this->currentUserService->getOrganization();
        $user = $this->currentUserService->getUser();

        if ($file->project_id !== $project->id || $file->organization_id !== $org->id) {
            abort(404);
        }

        $roleCode = strtolower($user->role?->code ?? '');
        if ($file->user_id !== $user->id && !in_array($roleCode, ['admin', 'owner', 'hr'])) {
            abort(403, 'Unauthorized to delete this file.');
        }

        if (Storage::disk('public')->exists($file->file_path)) {
            Storage::disk('public')->delete($file->file_path);
        }

        $file->delete();

        return redirect()->back()->with('success', 'File deleted successfully.');
    }
}
