@extends("mail.layout.app")

@section('content')
    <table bgcolor="#ffffff" align="center"
           width="100%" border="0" cellspacing="0"
           cellpadding="0">
        <tbody>
        <tr>
            <td align="center"
                style="text-align:center;vertical-align:top;font-size:0;">
                <table
                    align="center"
                    border="0"
                    cellspacing="0"
                    cellpadding="0">
                    <tbody>

                    <tr>
                        <td
                            align="center" style="height: 200px; padding: 22px 0 26px;">
                            <img
                                src="{{asset(config('sumon.mail.mail_header_logo'))}}"
                                alt=""
                                style="display:block; line-height:0; font-size:0; border:0;">
                        </td>
                    </tr>

                    <tr>
                        <td
                            height="20">
                            <multiline>
                                <strong style="font-size: 26px; letter-spacing: 0.52px; line-height: 1.5;">
                                    Welcome to {{config('app.name')}}!
                                </strong>
                            </multiline>
                        </td>
                    </tr>

                    <tr>
                        <td height="20"></td>
                    </tr>

                    <tr>
                        <td align="left" class="content-body">
                            <p>
                                We’re excited to have you onboard, now you have access to all the core functionality you
                                need to build invoice and make payments.
                            </p>
                            <p>
                                If you have any questions, please feel free to chat with us online, or send an email to
                                <a href="mailto:{{config('sumon.mail.support')}}">{{config('sumon.mail.support')}}</a>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td
                            height="12">
                        </td>
                    </tr>
                    <tr>
                        <td align="left" style="font-size: 14px; line-height: 22px; color: #232428; font-weight: bold;">
                            <multiline>
                                Regards,<br>
                                {{config('app.name')}} Team
                            </multiline>
                        </td>
                    </tr>
                    <tr>
                        <td
                            height="25">
                        </td>
                    </tr>
                    </tbody>
                </table>
            </td>
        </tr>
        </tbody>
    </table>
@endsection
