<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\FaxJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class AccountController extends Controller
{
    /**
     * Show the account dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();
        
        // Get user's fax history
        $faxJobs = FaxJob::where('sender_email', $user->email)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('account.dashboard', compact('user', 'faxJobs'));
    }



    /**
     * Show account settings
     */
    public function settings()
    {
        return view('account.settings');
    }

    /**
     * Update account settings
     */
    public function updateSettings(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);
        
        $user->update($request->only(['name', 'email']));
        
        return redirect()->route('account.settings')
            ->with('success', 'Your account settings have been updated.');
    }



    /**
     * Show fax job details
     */
    public function showFaxJob(FaxJob $faxJob)
    {
        $user = Auth::user();
        
        // Ensure user can only view their own fax jobs
        if ($faxJob->sender_email !== $user->email) {
            abort(404);
        }
        
        return view('account.fax-job', compact('faxJob'));
    }

    /**
     * Send secure access link to email (replaces insecure direct login)
     */
    public function sendAccessLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);
        
        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            // Don't reveal whether account exists - security best practice
            return back()->with('success', 'If an account exists with this email, you\'ll receive a secure access link shortly.');
        }
        
        // Generate secure token and send magic link for ALL users (passwordless system)
        $token = \App\Models\AccountAccessToken::createForEmail($request->email);
        
        try {
            Mail::to($request->email)->send(new \App\Mail\AccountAccessMail($request->email, $token));
            
            return back()->with('success', 'A secure access link has been sent to your email. Please check your inbox and click the link to access your account.');
        } catch (\Exception $e) {
            Log::error('Failed to send account access email: ' . $e->getMessage());
            return back()->withErrors(['email' => 'Unable to send access link. Please try again later.']);
        }
    }

    /**
     * Verify secure access token and log user in
     */
    public function verifyAccess(Request $request, string $token)
    {
        $email = \App\Models\AccountAccessToken::verifyAndConsume($token);
        
        if (!$email) {
            return redirect()->route('login')
                ->withErrors(['token' => 'This access link is invalid, expired, or has already been used. Please request a new one.']);
        }
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            return redirect()->route('login')
                ->withErrors(['token' => 'Account not found. Please contact support.']);
        }
        
        // Log the user in securely
        Auth::login($user);
        
        return redirect()->route('account.dashboard')
            ->with('success', 'Welcome back! You\'ve been securely logged in.');
    }
}