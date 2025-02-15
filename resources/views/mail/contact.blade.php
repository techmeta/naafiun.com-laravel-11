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
            <td height="20"></td>
          </tr>

          <tr>
            <td align="left" class="content-body">
              <p>
                {{ $contact->message }}
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
                ChinaExpress Team
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
