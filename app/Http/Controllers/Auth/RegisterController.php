<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Services\EmailVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Порт register.php вместе с обеими AJAX-ветками. */
class RegisterController extends Controller
{
    public function __construct(private readonly EmailVerificationService $verification)
    {
    }

    public function create(): View
    {
        return view('auth.register');
    }

    /** AJAX: отправить код на почту. */
    public function sendCode(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', Rule::unique('users', 'email')],
        ], [
            'email.unique' => 'Почта уже занята',
        ]);

        $this->verification->send($data['email']);

        return response()->json(['ok' => true]);
    }

    /** AJAX: проверить код. */
    public function verifyCode(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:6'],
        ]);

        $ok = $this->verification->verify($data['email'], $data['code']);

        return response()->json([
            'ok' => $ok,
            'error' => $ok ? null : 'Неверный или устаревший код',
        ], $ok ? 200 : 422);
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Проверяем подтверждение по базе, а не по скрытому полю формы.
        if (! $this->verification->isVerified($data['email'])) {
            return back()
                ->withInput()
                ->withErrors(['email' => 'Email не подтверждён. Запросите код повторно.']);
        }

        $user = User::create([
            'email' => $data['email'],
            'name' => $data['name'],
            'phone' => $data['phone'],
            'password' => $data['password'],
            'email_verified_at' => now(),
        ]);

        $this->verification->forget($data['email']);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('catalog.index')->with('status', 'Добро пожаловать!');
    }
}
