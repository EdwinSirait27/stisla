{{-- <!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Payroll Slip - {{ $payrollPeriod }}</title>
</head>
<body style="font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f6f8;">
    <div style="max-width: 700px; margin: 20px auto; background: #fff; border: 1px solid #e1e1e1; border-radius: 8px; overflow: hidden;">

        <!-- Header -->
        <div style="background-color: #004085; color: #fff; padding: 20px; text-align: center;">
            <h2 style="margin: 0; font-size: 20px;">Salary Statement - {{ $payrollPeriod }}</h2>
        </div>

        <!-- Content -->
        <div style="padding: 25px 30px;">
            <p>Dear <strong>{{ $employeeName }}</strong>,</p>
            <p>Please find below a summary of your salary for the period 
               <span style="font-weight: bold; color: #004085;">{{ $payrollPeriod }}</span>.
            </p>

            <table style="width: 100%; border-collapse: collapse; margin: 25px 0; font-size: 14px;">
                <tr>
                    <th style="border: 1px solid #e1e1e1; padding: 12px; text-align: left; background-color: #f8f9fa; font-weight: 600;">Description</th>
                    <th style="border: 1px solid #e1e1e1; padding: 12px; text-align: left; background-color: #f8f9fa; font-weight: 600;">Details</th>
                </tr>
                <tr>
                    <td style="border: 1px solid #e1e1e1; padding: 12px;">Total Salary</td>
                    <td style="border: 1px solid #e1e1e1; padding: 12px;"><em>View PDF Details</em></td>
                </tr>
                <tr>
                    <td style="border: 1px solid #e1e1e1; padding: 12px;">Total Deductions</td>
                    <td style="border: 1px solid #e1e1e1; padding: 12px;"><em>View PDF Details</em></td>
                </tr>
            </table>

            <p>For complete details, please download the attached PDF pay slip.</p>
            <p><strong>Note:</strong> To open the PDF file, please use your date of birth in the format 
               <code>yyyymmdd</code>. For example, if your birth date is August 6, 2000, then the password is <code>20000806</code>.
            </p>
            
            <p>If you have any questions regarding this pay slip, kindly contact the HR Department of 
               <strong>PT. Mahendradata Jaya Mandiri</strong>.
            </p>

            <p>Thank you,<br>
            HR Department<br>
            PT. Mahendradata Jaya Mandiri</p>
        </div>

        <!-- Footer -->
        <div style="background-color: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #555; border-top: 1px solid #e1e1e1;">
            <p style="margin: 5px 0;">This is an automated email. Please do not reply.</p>
            <p style="margin: 5px 0;">Mark this sender as important to ensure you receive next month’s pay slip notifications.</p>
            <p style="margin: 5px 0;">&copy; {{ date('Y') }} PT. Mahendradata Jaya Mandiri Createed by Edwin Sirait</p>
        </div>

    </div>
</body>
</html> --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Payroll Slip - {{ $payrollPeriod }}</title>
</head>
<body style="font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f6f8;">
    <div style="max-width: 700px; margin: 20px auto; background: #fff; border: 1px solid #e1e1e1; border-radius: 8px; overflow: hidden;">

        <!-- Header -->
        <div style="background-color: #004085; color: #fff; padding: 20px; text-align: center;">
            <h2 style="margin: 0; font-size: 20px;">Salary Statement - {{ $payrollPeriod }}</h2>
            <h3 style="margin: 5px 0 0; font-size: 16px; font-weight: normal;">Slip Gaji - {{ $payrollPeriod }}</h3>
        </div>

        <!-- ENGLISH VERSION -->
        <div style="padding: 25px 30px; border-bottom: 2px dashed #ddd;">
            <p>Dear <strong>{{ $employeeName }}</strong>,</p>
            <p>Please find below a summary of your salary for the period 
               <span style="font-weight: bold; color: #004085;">{{ $payrollPeriod }}</span>.
            </p>

            <table style="width: 100%; border-collapse: collapse; margin: 25px 0; font-size: 14px;">
                <tr>
                    <th style="border: 1px solid #e1e1e1; padding: 12px; background-color: #f8f9fa;">Description</th>
                    <th style="border: 1px solid #e1e1e1; padding: 12px; background-color: #f8f9fa;">Details</th>
                </tr>
                <tr>
                    <td style="border: 1px solid #e1e1e1; padding: 12px;">Total Salary</td>
                    <td style="border: 1px solid #e1e1e1; padding: 12px;"><em>View PDF Details</em></td>
                </tr>
                <tr>
                    <td style="border: 1px solid #e1e1e1; padding: 12px;">Total Deductions</td>
                    <td style="border: 1px solid #e1e1e1; padding: 12px;"><em>View PDF Details</em></td>
                </tr>
            </table>

            <p>For complete details, please download the attached PDF pay slip.</p>
            <p><strong>Note:</strong> To open the PDF file, please use your date of birth in the format 
               <code>yyyymmdd</code>. Example: August 6, 2000 → <code>20000806</code>.
            </p>
            
            <p>If you have any questions regarding this pay slip, kindly contact the HR Department of 
               <strong>PT. Mahendradata Jaya Mandiri</strong>.
            </p>

            <p>Thank you,<br>
            HR Department<br>
            PT. Mahendradata Jaya Mandiri</p>
        </div>
          <div style="background-color: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #555; border-top: 1px solid #e1e1e1;">
            <p style="margin: 5px 0;">This is an automated email. Please do not reply.</p>
            <p style="margin: 5px 0;">&copy; {{ date('Y') }} PT. Mahendradata Jaya Mandiri — Created by Edwin Sirait</p>
        </div>

        <!-- INDONESIAN VERSION -->
        <div style="padding: 25px 30px;">
            <p>Kepada <strong>{{ $employeeName }}</strong>,</p>
            <p>Berikut adalah ringkasan gaji anda untuk periode 
               <span style="font-weight: bold; color: #004085;">{{ $payrollPeriod }}</span>.
            </p>

            <table style="width: 100%; border-collapse: collapse; margin: 25px 0; font-size: 14px;">
                <tr>
                    <th style="border: 1px solid #e1e1e1; padding: 12px; background-color: #f8f9fa;">Keterangan</th>
                    <th style="border: 1px solid #e1e1e1; padding: 12px; background-color: #f8f9fa;">Detail</th>
                </tr>
                <tr>
                    <td style="border: 1px solid #e1e1e1; padding: 12px;">Total Gaji</td>
                    <td style="border: 1px solid #e1e1e1; padding: 12px;"><em>Lihat detail di PDF</em></td>
                </tr>
                <tr>
                    <td style="border: 1px solid #e1e1e1; padding: 12px;">Total Potongan</td>
                    <td style="border: 1px solid #e1e1e1; padding: 12px;"><em>Lihat detail di PDF</em></td>
                </tr>
            </table>

            <p>Untuk detail lengkap, silakan unduh lampiran slip gaji dalam format PDF.</p>
            <p><strong>Catatan:</strong> Untuk membuka file PDF, gunakan tanggal lahir Anda dengan format 
               <code>yyyymmdd</code>. Contoh: 6 Agustus 2000 → <code>20000806</code>.
            </p>
            
            <p>Jika ada pertanyaan mengenai slip gaji ini, silakan hubungi Departemen HR 
               <strong>PT. Mahendradata Jaya Mandiri</strong>.
            </p>

            <p>Terima kasih,<br>
            Departemen HR<br>
            PT. Mahendradata Jaya Mandiri</p>
        </div>

        <!-- Footer -->
        <div style="background-color: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #555; border-top: 1px solid #e1e1e1;">
            <p style="margin: 5px 0;">Email ini dikirim otomatis. Mohon untuk tidak membalas.</p>
            <p style="margin: 5px 0;">&copy; {{ date('Y') }} PT. Mahendradata Jaya Mandiri — Created by Edwin Sirait</p>
        </div>

    </div>
</body>
</html>
