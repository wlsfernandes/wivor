@props(['title' => 'WivorPhotos'])
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
    <title>{{ $title }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <style>
        body,
        table,
        td,
        a {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        table,
        td {
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }

        img {
            -ms-interpolation-mode: bicubic;
        }

        .email-content h1 {
            color: #232323;
            font-size: 26px;
            line-height: 1.25;
            margin: 0 0 22px;
        }

        .email-content p,
        .email-content li,
        .email-content td {
            color: #505050;
            font-size: 16px;
            line-height: 1.6;
        }

        .email-content p {
            margin: 0 0 18px;
        }

        .email-content a:not(.email-button) {
            color: #e95800;
        }

        .email-content .button-primary,
        .email-content .button-blue {
            background-color: #ff6700 !important;
            border-color: #ff6700 !important;
            color: #ffffff !important;
        }

        @media only screen and (max-width: 660px) {
            .email-shell {
                width: 100% !important;
            }

            .email-body-cell {
                padding: 32px 22px !important;
            }

            .email-footer-cell {
                padding-left: 22px !important;
                padding-right: 22px !important;
            }

            .email-content h1 {
                font-size: 23px !important;
            }
        }
    </style>
</head>
<body style="background-color: #f4f4f4; color: #505050; font-family: Arial, Helvetica, sans-serif; margin: 0; padding: 0; width: 100% !important;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation" style="background-color: #f4f4f4; width: 100%;">
        <tr>
            <td align="center" style="padding: 28px 12px;">
                <table class="email-shell" width="640" cellpadding="0" cellspacing="0" border="0" role="presentation" style="background-color: #ffffff; border-collapse: separate; border-radius: 8px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); max-width: 640px; overflow: hidden; width: 100%;">
                    <tr>
                        <td align="center" style="background-color: #232323; border-bottom: 5px solid #ff6700; padding: 24px;">
                            <a href="{{ url('/') }}" target="_blank" rel="noopener noreferrer" style="display: inline-block; text-decoration: none;">
                                <img src="{{ asset('assets/images/logo/wivor_white.png') }}" width="128" alt="WivorPhotos" style="border: 0; display: block; height: auto; max-width: 128px; outline: none; text-decoration: none; width: 128px;">
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td class="email-body-cell email-content" style="background-color: #ffffff; padding: 42px 48px 36px;">
                            {{ $slot }}
                        </td>
                    </tr>
                    <tr>
                        <td class="email-footer-cell" align="center" style="background-color: #232323; border-top: 1px solid #3a3a3a; padding: 30px 36px;">
                            <p style="color: #ffffff; font-family: Arial, Helvetica, sans-serif; font-size: 14px; line-height: 1.6; margin: 0 0 14px; text-align: center;">
                                160 Clairemont Ave., Suite 300<br>
                                Decatur, GA 30030
                            </p>
                            <p style="font-family: Arial, Helvetica, sans-serif; font-size: 14px; line-height: 1.6; margin: 0 0 20px; text-align: center;">
                                <a href="mailto:contact@wivorphotos.com" style="color: #ff8a3d; text-decoration: underline;">contact@wivorphotos.com</a>
                            </p>
                            <table cellpadding="0" cellspacing="0" border="0" role="presentation" align="center">
                                <tr>
                                    <td style="padding: 0 6px;">
                                        <a href="https://www.facebook.com/p/WiVor-Photos-61573081696201/" target="_blank" rel="noopener noreferrer" aria-label="WivorPhotos on Facebook" style="background-color: #3b3b3b; border-radius: 18px; color: #ffffff; display: inline-block; font-family: Arial, Helvetica, sans-serif; font-size: 13px; line-height: 36px; padding: 0 14px; text-decoration: none;">
                                            <img src="{{ asset('assets/images/social/facebook.png') }}" width="17" height="17" alt="" style="border: 0; display: inline-block; height: 17px; margin-right: 7px; outline: none; vertical-align: middle; width: 17px;">
                                            <span style="color: #ffffff; vertical-align: middle;">Facebook</span>
                                        </a>
                                    </td>
                                    <td style="padding: 0 6px;">
                                        <a href="https://www.instagram.com/wivor.photos/" target="_blank" rel="noopener noreferrer" aria-label="WivorPhotos on Instagram" style="background-color: #3b3b3b; border-radius: 18px; color: #ffffff; display: inline-block; font-family: Arial, Helvetica, sans-serif; font-size: 13px; line-height: 36px; padding: 0 14px; text-decoration: none;">
                                            <img src="{{ asset('assets/images/social/instagram.png') }}" width="17" height="17" alt="" style="border: 0; display: inline-block; height: 17px; margin-right: 7px; outline: none; vertical-align: middle; width: 17px;">
                                            <span style="color: #ffffff; vertical-align: middle;">Instagram</span>
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            <p style="color: #a9a9a9; font-family: Arial, Helvetica, sans-serif; font-size: 12px; line-height: 1.5; margin: 20px 0 0; text-align: center;">
                                &copy; WivorPhotos. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
