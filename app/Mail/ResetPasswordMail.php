<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $email;
    public $memberId;
    public $passwordPlain;

    public function __construct($email, $memberId, $passwordPlain)
    {
        $this->email = $email;
        $this->memberId = $memberId;
        $this->passwordPlain = $passwordPlain;
    }

    public function build()
    {
        $htmlContent = '
        <div style="
            font-family: "Inter", "Segoe UI", -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        ">
            <div style="
                max-width: 480px;
                margin: auto;
                background: #ffffff;
                border-radius: 16px;
                box-shadow: 0 20px 40px rgba(0,0,0,0.1);
                overflow: hidden;
                position: relative;
            ">
                <!-- Header dengan gradient -->
                <div style="
                    background: linear-gradient(135deg, #4A90E2 0%, #357ABD 100%);
                    padding: 30px;
                    text-align: center;
                    color: white;
                ">
                    <h2 style="margin: 0; font-size: 24px; font-weight: 600;">Password Telah Direset</h2>
                </div>

                <!-- Konten utama -->
                <div style="padding: 35px;">
                    <p style="
                        font-size: 16px;
                        color: #666;
                        line-height: 1.6;
                        margin-bottom: 25px;
                    ">
                        Halo <strong style="color: #333;">'.$this->email.'</strong>, password akun member Anda 
                        <strong style="color: #4A90E2;">'.$this->memberId.'</strong> telah berhasil direset.
                    </p>

                    <!-- Password box yang modern -->
                    <div style="
                        background: linear-gradient(135deg, #f8faff 0%, #f0f4ff 100%);
                        border: 2px dashed #4A90E2;
                        border-radius: 12px;
                        padding: 20px;
                        margin: 25px 0;
                        text-align: center;
                        position: relative;
                    ">
                        <div style="
                            position: absolute;
                            top: -12px;
                            left: 50%;
                            transform: translateX(-50%);
                            background: white;
                            padding: 0 15px;
                            color: #4A90E2;
                            font-size: 14px;
                            font-weight: 600;
                        ">PASSWORD BARU</div>
                        
                        <div style="
                            font-family: "Courier New", monospace;
                            font-size: 22px;
                            font-weight: 700;
                            color: #2c5aa0;
                            letter-spacing: 2px;
                            padding: 10px;
                            background: rgba(74, 144, 226, 0.1);
                            border-radius: 8px;
                            margin-top: 10px;
                        ">'.$this->passwordPlain.'</div>
                    </div>

                    <!-- Info box -->
                    <div style="
                        background: #f8f9fa;
                        border-left: 4px solid #28a745;
                        padding: 15px;
                        border-radius: 0 8px 8px 0;
                        margin: 25px 0;
                    ">
                        <p style="
                            margin: 0;
                            font-size: 14px;
                            color: #666;
                            line-height: 1.5;
                        ">
                            💡 <strong>Tips Keamanan:</strong> Segera login dan ubah password Anda setelah berhasil masuk ke akun.
                        </p>
                    </div>

                    <!-- Call to action -->
                    <div style="text-align: center; margin-top: 30px;">
                        <a href="http://localhost:8080/login" style="
                            display: inline-block;
                            background: linear-gradient(135deg, #4A90E2 0%, #357ABD 100%);
                            color: white;
                            padding: 12px 30px;
                            text-decoration: none;
                            border-radius: 25px;
                            font-weight: 600;
                            font-size: 15px;
                            box-shadow: 0 4px 15px rgba(74, 144, 226, 0.3);
                            transition: all 0.3s ease;
                        " onmouseover="this.style.transform="scale(1.05)"; this.style.boxShadow="0 6px 20px rgba(74, 144, 226, 0.4)";" 
                           onmouseout="this.style.transform="scale(1)"; this.style.boxShadow="0 4px 15px rgba(74, 144, 226, 0.3)";">
                            Login ke Akun
                        </a>
                    </div>
                </div>

                <!-- Footer -->
                <div style="
                    background: #f8f9fa;
                    padding: 20px;
                    text-align: center;
                    border-top: 1px solid #eaeaea;
                ">
                    <p style="
                        margin: 0;
                        font-size: 12px;
                        color: #999;
                        line-height: 1.4;
                    ">
                        Jika Anda tidak melakukan permintaan reset password, 
                        <a href="#" style="color: #4A90E2; text-decoration: none;">hubungi support</a> 
                        segera.<br>
                        &copy; '.date('Y').' '.config('app.name').'. All rights reserved.
                    </p>
                </div>
            </div>
        </div>';

        return $this->subject('Reset Password - Akun Member')
            ->html($htmlContent);
    }
}
