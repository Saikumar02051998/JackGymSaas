<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function __construct(protected NotificationService $notifications) {}

    public function index()
    {
        $announcements = Announcement::with(['creator', 'role'])
            ->where('gym_id', current_gym()?->id)
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('announcements.index', compact('announcements'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('announcements.manage'), 403);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'audience' => ['required', 'in:all,staff,coaches,clients,role,client'],
            'role_id' => ['nullable', 'exists:roles,id'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['nullable', 'in:draft,published,archived'],
        ]);

        $announcement = Announcement::create(array_merge($data, [
            'gym_id' => current_gym()->id,
            'status' => $data['status'] ?? 'published',
            'created_by' => auth()->id(),
        ]));

        if ($announcement->status === 'published') {
            $this->notifyAudience($announcement);
        }

        audit_log('announcement.created', 'announcements', $announcement->id, "Created announcement {$announcement->title}");

        return back()->with('success', 'Announcement published.');
    }

    public function update(Request $request, Announcement $announcement)
    {
        abort_unless(auth()->user()->hasPermission('announcements.manage'), 403);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'audience' => ['required', 'in:all,staff,coaches,clients,role,client'],
            'role_id' => ['nullable', 'exists:roles,id'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['nullable', 'in:draft,published,archived'],
        ]);

        $announcement->update($data);

        return back()->with('success', 'Announcement updated.');
    }

    public function destroy(Announcement $announcement)
    {
        abort_unless(auth()->user()->hasPermission('announcements.manage'), 403);

        $announcement->delete();

        return back()->with('success', 'Announcement deleted.');
    }

    private function notifyAudience(Announcement $announcement): void
    {
        $users = match ($announcement->audience) {
            'clients' => \App\Models\User::where('gym_id', $announcement->gym_id)
                ->whereHas('roles', fn ($q) => $q->where('slug', 'client'))->get(),
            'staff' => \App\Models\User::where('gym_id', $announcement->gym_id)
                ->whereHas('roles', fn ($q) => $q->where('slug', '!=', 'client'))->get(),
            'coaches' => \App\Models\User::where('gym_id', $announcement->gym_id)
                ->whereHas('roles', fn ($q) => $q->where('slug', 'coach'))->get(),
            'role' => \App\Models\User::where('gym_id', $announcement->gym_id)
                ->whereHas('roles', fn ($q) => $q->where('id', $announcement->role_id))->get(),
            'client' => $announcement->client?->user ? collect([$announcement->client->user]) : collect(),
            default => \App\Models\User::where('gym_id', $announcement->gym_id)->get(),
        };

        foreach ($users as $user) {
            $this->notifications->announcementPublished($user, $announcement->title);
        }
    }
}
