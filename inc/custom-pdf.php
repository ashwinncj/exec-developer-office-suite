<?php
// Include TCPDF library files
require_once plugin_dir_path(__DIR__) . 'tcpdf/tcpdf.php';

class CustomPDF extends TCPDF {
    private $company_name;
    private $company_address;
    private $phone;
    private $email;
    private $logo;

    public function setCompanyDetails($name, $address, $phone, $email, $logo) {
        $this->company_name = $name;
        $this->company_address = $address;
        $this->phone = $phone;
        $this->email = $email;
        $this->logo = $logo;
    }

    // Page header
    public function Header() {
        // $separator = both email and phone should be present.
        $separator = $this->email && $this->phone ? ' | ' : '';
        $html = '';
        if ($this->logo) {
            $img_type = strtolower(pathinfo($this->logo, PATHINFO_EXTENSION));
            if (in_array($img_type, ['png', 'jpeg', 'jpg'])) {
                $html .= '<table width="100%;"><tr><td align="center"><img style="display:block;" src="' . $this->logo . '" height="50"></td></tr></table>';
            }
        }
        $this->writeHTML($html, true, false, false, false, '');
        // if only image yes then -10
        // if image and companny name then -5
        // if only company name then nothing
        if ($this->logo && $this->company_name) {
            $this->SetY($this->GetY() - 5);
        } elseif ($this->logo) {
            $this->SetY($this->GetY() - 10);
        }
        $this->SetY($this->GetY());
        $html = '<table border="0" cellpadding="3" cellspacing="0" width="100%">';        
        $html .= '<tr>
                    <td width="100%;margin:0" align="center">
                      <span style="font-weight: bold; font-size: 20px;">' . $this->company_name . '</span><br>
                      <span style="font-size: 9px;">' . nl2br($this->company_address) . '</span><br>
                      <span style="font-size: 9px;">' . $this->phone . $separator . $this->email . '</span>
                    </td>
                  </tr>
                </table>';
        
        $this->writeHTML($html, true, false, false, false, '');
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, 0, 'C');
    }
}

function exec_dev_office_suite_export_letter($request) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'exec_data';
    $id = intval($request['id']);

    // Fetch the letter details by ID
    $letter = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE id = %d",
        $id
    ));

    if (!$letter) {
        return new WP_Error('no_letter', 'Letter not found', array('status' => 404));
    }

    // Fetch company details from options
    $company_name = get_option('exec_dev_office_suite_company_name');
    $company_address = get_option('exec_dev_office_suite_company_address');
    $phone = get_option('exec_dev_office_suite_phone');
    $email = get_option('exec_dev_office_suite_email');
    $logo = get_option('exec_dev_office_suite_logo');
    $display_logo = get_option('exec_dev_office_suite_display_logo');
    $display_company_name = get_option('exec_dev_office_suite_display_company_name');
    // $reference_number = prefix from the settings and $letter id. YYYY/MM/ Min 3 digits. e.g. 002, 040,...
    $reference_number = get_option('exec_dev_office_suite_reference_number_prefix');
    $reference_number .= '/' . date('Y', strtotime($letter->date));
    $reference_number .= '/' . date('m', strtotime($letter->date));
    $reference_number .= '/' . str_pad($letter->id, 3, '0', STR_PAD_LEFT);
    $offset = 45;
    
    // if not checked then hide the logo
    if ( '1' !== $display_logo) {
        $logo = false;
        $offset = $offset - 15;
    }

    // if not checked then hide the company name
    if ( '1' !== $display_company_name) {
        $company_name = false;
        $offset = $offset - 5;
    }

    // Create new PDF document
    $pdf = new CustomPDF();
    $pdf->SetAutoPageBreak(TRUE, 20);
    $pdf->SetHeaderMargin(5);
    $pdf->setCompanyDetails($company_name, $company_address, $phone, $email, $logo);
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetMargins(15, 15, 15);

    $pdf->setY($offset);
    // Create HTML content for the letter details
    $html = '
    <div class="letter-header">
        <p><b>' . date('d M Y', strtotime($letter->date)) . '</b></p>
        <p><b>Ref: ' . $reference_number . '</b></p>';
    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->setY($pdf->GetY() + 5);
    $html = '
        <p><b>To,<br></b>
        <b>' . $letter->to_field . '</b></p>';
    $pdf->writeHTML($html, true, false, true, false, '');
    
    $pdf->setY($pdf->GetY() );
    $html = '
        <p>' . nl2br($letter->address) . '</p>';
    $pdf->writeHTML($html, true, false, true, false, '');
    
    $pdf->SetMargins(20, 30, 30);
    $pdf->setY($pdf->GetY() + 5);
    $html = '<p class="subject"><b><u>Subject:</u> ' . $letter->subject . '</b></p>
    </div>';
    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->SetMargins(15, $offset, 15);

    $pdf->setY($pdf->GetY() - 10);
    $html ='
    <div class="letter-content">' . $letter->content . '</div>';

    // Add the letter details to the PDF
    $pdf->writeHTML($html, true, false, true, false, '');

    // Output PDF as a string
    $pdf_output = $pdf->Output('letter.pdf', 'S');

    // Return the PDF as base64 encoded string
    return new WP_REST_Response(array('pdf' => base64_encode($pdf_output)), 200);
}

function exec_dev_office_suite_register_export_letter_endpoint() {
    register_rest_route('exec-dev-office-suite/v1', '/export-letter/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'exec_dev_office_suite_export_letter',
        'permission_callback' => 'exec_dev_office_suite_admin_permission_callback'
    ));
}
add_action('rest_api_init', 'exec_dev_office_suite_register_export_letter_endpoint');
?>
