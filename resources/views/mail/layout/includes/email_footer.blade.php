<table align="center"
       width="100%" border="0" cellspacing="0"
       cellpadding="0">
    <tr>
        <td
            height="32">
        </td>
    </tr>
    <tr>
        <td height="30">
            <table align="center"
                   width="306px" border="0" cellspacing="0"
                   cellpadding="0">
                <tbody>
                <tr>
                    <td align="center">
                        <multiline>
                            <a href="{{config('sumon.mail.contact_url')}}"
                               style="text-decoration:underline;font-size:15px; color:#0d8ee9; line-height:18px;font-weight: bold;">
                                Contact Us
                            </a>
                        </multiline>
                    </td>
                    <td align="center">
                        <multiline>
                            <a href="{{config('sumon.mail.privacy_url')}}"
                               style="text-decoration:underline;font-size:15px; color:#0d8ee9; line-height:18px;font-weight: bold;">
                                Privacy Policy
                            </a>
                        </multiline>
                    </td>
                    <td align="center" style="">
                        <multiline>
                            <a href="{{config('sumon.mail.faq_url')}}"
                               style="text-decoration:underline;font-size:15px; color:#0d8ee9; line-height:18px;font-weight: bold;">
                                Our FAQ
                            </a>
                        </multiline>
                    </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    <tr>
        <td
                height="24">
        </td>
    </tr>

    <tr>
        <td align="center">
            <table
                    align="center"
                    width="100%" border="0" cellspacing="0"
                    cellpadding="0">
                <tbody>
                <tr>
                    <td align="right"
                        height="40px"
                        width="48%"
                        style="padding-right: 8px;">
                        <img style="width: 150px" src="{{asset(config('sumon.mail.mail_footer_logo'))}}"
                             alt="{{config('app.name')}}">
                    </td>
                    <td align="center" width="4%">
                        <div style="background:#8f8f8f;height: 51px; width: 2px"></div>
                    </td>
                    <td
                            align="left"
                            width="48%"
                            style="padding-left: 8px;">
                        <table align="left" border="0" cellspacing="0" cellpadding="0">
                            <tbody>
                            <tr>
                                <td width="22px" height="28px" align="center" valign="middle">
                                    <img
                                            style=" width: 22px; height: 22px; margin-top: 4px; margin-right: 4px"
                                            src="{{asset(config('sumon.mail.mail_icon'))}}" alt="{{config('app.name')}}">
                                </td>
                                <td align="left" valign="middle">
                                    <multiline>
                                        <a href="mailto:{{config('sumon.mail.support')}}"
                                           style="color: #0d8ee9;text-decoration: none;">
                                            {{config('sumon.mail.support')}}
                                        </a>
                                    </multiline>
                                </td>
                            </tr>
                            <tr>
                                <td width="22px" height="28px" align="center" valign="middle">
                                    <img
                                            style=" width: 22px; height: 22px; margin-top: 4px; margin-right: 4px"
                                            src="{{asset(config('sumon.mail.phone_icon'))}}" alt="{{config('app.name')}}">
                                </td>
                                <td align="left" valign="middle">
                                    <multiline>
                                        <a href="tel:{{config('sumon.mail.mail_phone')}}"
                                           style="color: #0d8ee9;text-decoration: none;">
                                            {{config('sumon.mail.mail_phone')}}
                                        </a>
                                    </multiline>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>

    <tr>
        <td style="height: 24px"></td>
    </tr>

    <tr>
        <td style="padding-left: 34px; padding-right: 34px" width="100%">
            <div style="border-bottom:2px solid #0d8ee9;"></div>
        </td>
    </tr>
    <tr>
        <td height="28"></td>
    </tr>

    <tr>
        <td height="18"></td>
    </tr>
    <tr>
        <td align="center">
            <table
                    width="100%"
                    align="center"
                    border="0"
                    cellspacing="0"
                    cellpadding="0">
                <tbody>
                <tr>
                    <td align="right" width="48%">
                        <a href="{{config('sumon.mail.privacy_url')}}"
                           target="_blank"
                           style="color:#0d8ee9;text-decoration:none;">
                            Privacy Policy
                        </a>
                    </td>
                    <td align="center" width="4%">|</td>
                    <td align="left" width="48%">
                        <a href="{{config('sumon.mail.terms_url')}}"
                           target="_blank"
                           style="color:#0d8ee9; text-decoration:none;">
                            Terms and Conditions
                        </a>
                    </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    <tr>
        <td height="6"></td>
    </tr>
    <tr>
        <td align="center">
            <multiline>
                <p style="font-size:14px; color:#333333; line-height:22px;text-align: center;">
                    {!! config('sumon.mail.office_address') !!}
                </p>
                <p style="font-size:14px; color:#333333; line-height:22px;text-align: center;">
                    &copy; Copyright {{date('Y')}} {{config('app.name')}} All Rights Reserved.
                </p>
            </multiline>
        </td>
    </tr>

    <tr>
        <td height="32"></td>
    </tr>

    </tbody>
</table>
