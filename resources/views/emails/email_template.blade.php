<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Email Template</title>

<style>
  .helpinfo{
  width: 116px;
  height: 116px;
  position: relative;
  background: #fff;
  border: 1.5px solid #9F0101;
  border-radius: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto;
}
.helpinfo .vnouter {
  z-index: -1;
  width: 116px;
  height: 116px;
  position: absolute;
  background: #FFFFFF;
  border: 1.5px solid #9F0101;
  border-radius: 100%;
  top: 0;
  bottom: 0;
  left: -22px;
  right: 0;
  display: flex;
  align-items: center;
}
.redtble td{
  padding: 15px;
  border-bottom: 1px solid #CE1F1E;
  color: #fff;
  font-family: 'Arial';
  font-size: 16px; 
}
p strong {
  color: #C70B0A;
}
</style>

</head>
<body style="margin:0">
     <table style="margin:0 auto;" cellpadding="0" cellspacing="0">
        <tbody>
          <tr>
            <td style="text-align: center;width: 100%;">                  
              @if(!empty(config('settings.logo')))
              <img src="{{ asset(config('settings.logo')) }}" alt="{{ config('settings.appname') }}" style="width:auto; height:65px;">
              @endif
            </td>
          </tr>             
          <tr>
            <td style="color: #303030; font-size: 18px;line-height: 30px;font-family: 'Arial'; padding: 20px;">
              <p>
                  {!!$content!!}
              </p>
            </td>
          </tr>          
        </tbody>
      </table>
      <table style="width:100%">
        <tr>
            <td style="border-top: 1px solid grey;"></td>
        </tr>
        <tr>
          <td style="font-family: 'Arial';padding: 10px 0px 0px 10px;">
            <b>Need Help?</b>
            <p>If you have any questions or need assistance, please visit our Help Center or contact our support team at  <a href="mailto:{{ config('settings.site_email') }}">{{ config('settings.site_email') }}</a>.</p>
          </td>
        </tr>      
      </table>
</body>
</html>
