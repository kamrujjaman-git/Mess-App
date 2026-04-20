<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesControllerErrors;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class UserController extends Controller
{
    use HandlesControllerErrors;

    /** @return list<string> */
    private static function allowedRoles(): array
    {
        return ['admin', 'manager', 'member', 'user'];
    }

    public function create(): View
    {
        return view('users.create');
    }

    public function edit(User $user): View
    {
        return view('users.edit', compact('user'));
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse|View
    {
        try {
            $users = User::query()->orderBy('name')->orderBy('id')->get();
            if ($users->isEmpty()) {
                $this->logMissingData('users.index_missing_data');
            }

            if ($request->wantsJson()) {
                return response()->json($users);
            }

            return view('users.index', compact('users'));
        } catch (Throwable $e) {
            $this->logControllerError($e, 'users.index_failed');
            return $this->errorResponse($request, 'dashboard');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
                'role' => ['required', 'string', Rule::in(self::allowedRoles())],
            ]);

            $user = User::create($validated);

            if ($request->wantsJson()) {
                return response()->json($user, 201);
            }

            return redirect()->route('users.index')->with('success', 'User saved successfully');
        } catch (Throwable $e) {
            $this->logControllerError($e, 'users.insert_failed', [
                'email' => $request->input('email'),
            ]);
            return $this->errorResponse($request, 'users.index');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): JsonResponse
    {
        return response()->json($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user): JsonResponse|RedirectResponse
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
                'role' => ['required', 'string', Rule::in(self::allowedRoles())],
            ]);

            $user->update($validated);

            if ($request->wantsJson()) {
                return response()->json($user->fresh());
            }

            return redirect()->route('users.index')->with('success', 'User saved successfully');
        } catch (Throwable $e) {
            $this->logControllerError($e, 'users.update_failed', [
                'id' => $user->id,
            ]);
            return $this->errorResponse($request, 'users.index');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, User $user): JsonResponse|RedirectResponse
    {
        try {
            $user->delete();

            if ($request->wantsJson()) {
                return response()->json(null, 204);
            }

            return redirect()->route('users.index')->with('success', 'User saved successfully');
        } catch (Throwable $e) {
            $this->logControllerError($e, 'users.delete_failed', [
                'id' => $user->id,
            ]);
            return $this->errorResponse($request, 'users.index');
        }
    }
}
