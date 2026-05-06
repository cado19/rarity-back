<?php
require '../../vendor/autoload.php'; // Dompdf
include_once '../../config/cors.php';
include_once '../../config/Database.php';
include_once '../../models/Contract.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$database = new Database();
$db       = $database->connect();

$contractModel             = new Contract($db);
$contractModel->booking_id = $_GET['id'] ?? null;

$contract = $contractModel->read_single();

if (! $contract) {
    header('Content-Type: application/json');
    echo json_encode(["status" => "Error", "message" => "Contract not found"]);
    exit;
}

// Build signature URL first
$signatureUrl = "https://client.raritycars.com/contract/signatures/" . $contract['signature'];
$excessText   = ($contract['cdw'] === "true")
    ? "Excess (CDW option): Ksh. " . $contract['cdw_vehicle_excess']
    : "Excess (Non-CDW option): Ksh. " . $contract['vehicle_excess'];

// Non-CDW clause
$nonCdwBlock = "
<p>All cars are insured. But in all cases the first Ksh. {$contract['vehicle_excess']} Excess premiums depending on the vehicle hired on every claim are the responsibility of the hirer. (NON-CDW OPTION EXCESS/DEDUCTIBLE RATES – Small Saloons: Kes.100,000, Standard Saloon: Kes.150,000, Mid-SUV: Kes.250,000, SUV: Kes.400,000, Luxury SUV: Kes.600,000).</p>
<table style='width:100%; margin-top:10px; font-size:13px; border-collapse:collapse;'>
  <tr>
    <td style='width:50%; padding:6px;'>Hirer's Signature:<br><img src='{$signatureUrl}' alt='Signature' style='height:60px;' /></td>
    <td style='width:50%; padding:6px;'>Date: {$contract['start_date']}</td>
  </tr>
</table>
";

// CDW clause
$cdwBlock = "
<p style='color:#0000cc;'>CDW OPTION (Optional insurance add‑on that reduces the hirer’s financial liability – Excess/deductible should the vehicle be damaged or stolen). (CDW OPTION EXCESS/DEDUCTIBLE RATES – Small Saloons: Kes.45,000, Standard Saloon: Kes.50,000, Mid-SUV: Kes.75,000, SUV: Kes.100,000, Luxury SUV: Kes.150,000). DAILY CDW RATES – Small Saloon: Kes.250, Standard Saloon: Kes.500, Mid SUV: Kes.750, SUV: Kes.1,000, Luxury SUV: Kes.1,500.</p>
<table style='width:100%; margin-top:10px; font-size:13px; border-collapse:collapse;'>
  <tr>
    <td style='width:50%; padding:6px;'>Hirer's Signature:<br><img src='{$signatureUrl}' alt='Signature' style='height:60px;' /></td>
    <td style='width:50%; padding:6px;'>Date: {$contract['start_date']}</td>
  </tr>
</table>
<p>All cars are insured. But in all cases the first Ksh. {$contract['cdw_vehicle_excess']} Excess & loss of use premium depending on the vehicle hired on every claim.</p>
";

$html =
    "
    <div style='font-family:Arial, sans-serif; font-size:13px; line-height:1.4;'>

      <!-- Logo -->
      <div style='text-align:center; margin-bottom:20px;'>
        <img src='https://backend.raritycars.com/files/rarity_contract_top.png' alt='Company Logo' style='width:100%; max-height:120px;' />
        <h2 style='margin:0; font-size:22px;'>Booking Contract</h2>
      </div>

      <!-- Hirer / Driver Details -->
      <table style='width:100%; border:1px solid #000; border-collapse:collapse; margin-bottom:15px;'>
        <tr><td style='padding:6px;'><b>HIRER FULL NAME</b></td></tr>
        <tr><td style='padding:6px;'><b>{$contract['c_fname']} {$contract['c_lname']}</b></td></tr>
        <tr><td style='padding:6px;'>DRIVER: {$contract['first_name']} {$contract['last_name']}</td></tr>
        <tr><td style='padding:6px;'>HIRER PHYSICAL ADDRESS: {$contract['residential_address']}</td></tr>
      </table>

      <!-- Vehicle / Rate Details -->
      <table style='width:100%; border:1px solid #000; border-collapse:collapse; margin-bottom:15px;'>
        <tr>
          <td style='padding:6px;'><b>SHS PER DAY</b></td>
          <td style='padding:6px;'><b>CAR MAKE</b></td>
          <td style='padding:6px;'><b>CAR MODEL</b></td>
        </tr>
        <tr>
          <td style='padding:6px;'>{$dailyRate}</td>
          <td style='padding:6px;'>{$contract['make']}</td>
          <td style='padding:6px;'>{$contract['model']}</td>
        </tr>
        <tr>
          <td style='padding:6px;'><b>DATE IN:</b> {$contract['end_date']}</td>
          <td style='padding:6px;'><b>TIME IN:</b> {$contract['end_time']}</td>
          <td style='padding:6px;'><b>DATE OUT:</b> {$contract['start_date']}</td>
          <td style='padding:6px;'><b>TIME OUT:</b> {$contract['start_time']}</td>
        </tr>
      </table>

      <!-- Liability -->
      <div style='border:1px solid #000; padding:10px; margin-bottom:15px;'>
        THE UNDERSIGNED AM HIRING CAR NO {$contract['number_plate']} IN GOOD CONDITION AND AM LIABLE TO PAY ANY DAMAGE CAUSED TO THIS CAR.
      </div>

      <!-- Statutory Declaration -->
      <h3 style='text-align:center;'>STATUTORY DECLARATION</h3>
      <p>I declare that:</p>
      <p>The motor vehicle will be used for the following purpose...................................................................</p>
      <p>I am not less than 23 years old or over 70 years old.</p>
      <p>I do not suffer any physical infirmity or defective hearing likely to affect my driving ability. I have not been convicted during the last five years of careless, reckless or dangerous driving. I have held a driving license for at least two years and it is current, valid and free from endorsement. . In connection with motor insurance, no insurer at any time has</p>
      <p>Declined my proposal and increased or imposed special conditions, refused to renew my policy or cancelled my policy. I FURTHER
         DECLARE to the best of my knowledge and belief that:</p>
            <p>a) The above particulars, answers and statements are true whether or not completed in my own hand</p>
            <p>b) The motor vehicle will not be used for carriage of passengers for hire or reward.</p>
            <p>c) The motor vehicle will not be used to carry more than the number of passengers declared</p>
            <p>My driving license has been examined by the car hire operator.</p>
            <p>The motor vehicle will only be driven by the persons who have completed t h i s declaration form.</p>
            <p><b> NOTE: ANY EXTENSION BEYOND TWO HOURS WILL BE CHARGED FULLY DAY.</b></p>
            <p>I undertake that this declaration shall be the basis of the contract with the insurers and shall be deemed to be incorporated in the
         existing policy/of insurance quoted above.</p>
         <div style='display:flex; justify-content:space-between; margin-top:20px; font-size:13px;'>
          <div style='width:48%;'>
            DATE: {$contract['start_date']}
          </div>
          <div style='width:48%;'>
            SIGNATURE: <img src='{$signatureUrl}' alt='Signature' style='height:60px;' />
          </div>
        </div>
        <p>
            I FULLY UNDERSTAND THAT I AM THE AUTHORISED PERSON TO DRIVE THIS VEHICLE UNLESS OTHER DRIVERS ARE SPECIFIED ABOVE IN
         THE AGREEMENT. THIS VEHICLE IS HIRED FOR THE SPECIFIED PERIOD STATED ABOVE AND IF EXCEEDED THE VEHICLE WILL BE
         CONSIDERED AS STOLEN AND THE MATTER WILL BE REPORTED TO THE POLICE AS UNLAWFUL USE OF MOTOR VEHICLE CAUSING
         OFFENSE.
        </p>

        <p>
            INCASE OF AN ACCIDENT, I THE HIRER COMMIT MYSELF TO PAY A SUM OF KSHS …<?php show_numeric_value($contract, 'vehicle_excess'); ?>... TO COVER THE EXCESS PROTECTOR & LOSS OF USE TO THE COMPANY.
        </p>

        <!-- Signature block -->
        <table style='width:100%; margin-top:20px; border-collapse:collapse; font-size:13px;'>
          <tr>
            <td style='width:70%;'></td>
            <td style='width:30%; text-align:center;'>
              [signature]<br>
              <img src='{$signatureUrl}' alt='Signature' style='height:60px;' />
            </td>
          </tr>
        </table>

        <!-- C.S / INV / OUT / IN / CALCULATIONS -->
        <table style='width:100%; margin-top:20px; border:1px solid #000; border-collapse:collapse; font-size:13px;'>
          <tr>
            <td style='padding:6px; border:1px solid #000;'>C.S NO.</td>
            <td style='padding:6px; border:1px solid #000;'>INV NO.</td>
            <td style='padding:6px; border:1px solid #000;'>OUT BY</td>
            <td style='padding:6px; border:1px solid #000;'>IN BY</td>
            <td style='padding:6px; border:1px solid #000;'>CALCULATIONS BY</td>
          </tr>
        </table>

        <p>
            NB: THE VEHICLE IS NOT AUTHORISED TO TRAFFIC ANY PROHIBITED GOODS OR BE USED AS TAXI.
             THE VEHICLE MUST NOT BE USED TO AID ANY CRIMINAL ACTIVITIES AS PRESCRIBED UNDER THE LAWS OF
             KENYA AND IF FOUND, THE HIRER WILL BE PERSONALLY LIABLE AS PER THE LAW.
             THE HIRER INDEMNIFIES AND SHALL KEEP THE COMPANY INDEMNIFIED OF ANY CRIMINAL OR CIVIL
             LIABILITY THAT MAY OCCUR AS A RESULT OF ANY MISUSE OF THE VEHICLE OR ACTIONS OF THE HIRER
        </p>

      <!-- Signature -->
      <div style='margin-top:20px;'>
        <b>Signature:</b><br>
        <img src='{$signatureUrl}' alt='Signature' style='height:60px;' />
      </div>

      <!-- Terms & Conditions -->
      <h3 style='text-align:center; margin-top:30px;'>TERMS & CONDITIONS</h3>
      <div style='font-size:12px; line-height:1.5; text-align:justify;'>
        <p>1. Rarity rent a car hereby agrees to let this vehicle on self-drive hire to the person named overleaf...</p>
        <p>2. The period of hire shall be as set out... hourly fees of KES. 1000 for every extra hour incurred.</p>
        <P>3. In accepting the vehicle, the hirer shall be deemed to have satisfied himself that the vehicle is road worthy and in a proper and safe condition and
working order and the company shall not be liable to make any payments to the Hirer in an event or in case of any breakdown. The company will
also not be accused or blamed to have provided a faulty or un-road worthy car to the hirer for any events occurring during the rental period.</P>
        <P>4. The hire charges shall be based on the number of days the vehicle is hired for and the mileage done during the period between the dates when
the vehicle is taken from the company’s premises and returned there. The rates per day plus kilometer are set out overleaf where the odome
ter records in Kilometers. If the speedometer seal has been tampered with the charges shall be at the rate of Ksh. 50,000 and the hirer shall be
responsible for replacing it.</P>
        <P>5. The hirer further agrees that
 He will not drive (and ensure that any unauthorized driver will not drive) the vehicle whilst under the influence of alcohol, Hallucinations, drugs narcotic,
barbiturates and any other substances impairing the driver’s consciousness or ability to control the vehicle. The vehicle will be driven skillfully and all
Traffic Laws and Rules and the provisions of the Highway code shall at all times be complied with and observed.</P>
        <p><b>The vehicle shall</b></p>
        <P>a. Not be overloaded or carry more passengers than its passenger capacity specified on the insurance license on the windscreen</P>
        <P>b. he vehicle will be driven by the hirer or any other driver named overleaf who must have a current bona fides driver’s license for a minimum of 2 years and must be not less than 23 years and not more than 70 years of age.</P>
        <P> c. The vehicle will be kept locked and secured when packed and every precaution will be taken to avoid theft of it or any item in it and damage.</P>
        <P>d. The vehicle shall be used for social and pleasure purposes and only on weather roads, the vehicle shall not be used for racing or pace making nor for carrying fare-paying passengers.</P>
        <P>e. The vehicle shall not be taken out of Kenya,</P>
        <P> f. The Hirer shall promptly and timely pay parking and traffic fines and if he shall fail to do so, he shall be responsible to pay additional 1000/= plus each fine not paid and indemnify the company for any loss or damage it may suffer as a result of this.</P>
        <P> g. The Hirer shall at all times check the oil and water and tyre pressure and In the event fails to do so he shall be responsible to reimburse and  indemnify the company for any loss, damage or expenses that it may suffer.</P>
        <P>h. Unless authorized by the company in writing. The hirer under no circumstances shall modify, or repair the vehicle. The company shall not be liable for any defects arising as a result of such undeclared modifications or repairs.</P>
        <P> 6. Notwithstanding anything herein contained in case of breakdown or any accident or damage as a result of a willful act or gross neglect of the: -The hirer or authorized driver, the Hirer shall pay the company the total cost of towing the car to the company’s premises and the full cost of repairing the vehicle or replacement of the vehicle if un-repairable,</P>
        <P>a. The Hirer shall be responsible to pay for the repair of punctures, replacing burst tyres, stolen or lost spare tyres, damaged or broken windscreen or glasses, damaged tools (including jack and handle), tape recorder and radio.</P>
        <P>b. In case of an accident (Involving the vehicle) the Hirer or the authorized driver shall report the accident to the police and the company within 24 hours no matter how minimal the damage is and supply the company with the police officer’s name, number and details of the police station and a police abstract. Under no circumstances shall liability be admitted. The hirer shall be at the earlier opportunity and in any case not later than 48 hours after the occurrence of the accident give a full statement in writing of how the accident occurred and also provide a copy of the police abstraction form duty completed. If and when required, shall make available the authorized driver to give any statement as may be required by the company.</P>
        <P>c. If any anti-theft device installed in the vehicle is not utilized by the hirer and the vehicle is stolen, the police will be informed then the hirer will be called upon to pay the costs of the vehicle.</P>
        <P>i. The insurance policy covering the vehicle has been made available to the hirer and hereby acknowledges that he or she and the authorized driver shall at all times comply with the terms and conditions of this company and the insurance policy.</P>
        <P>ii. Damage to the vehicle or loss by fire or theft of the vehicle or any material damage to any other vehicle or property and the driver and the passengers are not covered. In case of a third-party injury claim, the hirer shall be responsible to bear the claim and excess premium as stated overleaf. THE COMPANY WILL NOT BE RESPONSIBLE FOR ANY SUCH LIABITY.</P>
        <P>iii. The insurance, whether or not the CDW option is exercised does not cover it.</P>
        <P> a) Any loss of items from the vehicle.</P>
        <P>b) Any breakdown or damage to the vehicle otherwise than by collision,</P>
        <P>c) Injuries or loss to the hirer or driver or the passengers.</P>
        <P> d) Burst tyres, stolen, lost spare wheel, damaged OR broken windscreen or glasses, damaged rims, tools (including jack & handle)</P>
        <p>All cars are insured. But in all cases the first {$excessText} is the responsibility of the hirer.</p>
        <p>CDW OPTION (Optional insurance add‑on that reduces liability)...</p>
        <p>Daily CDW Rates: Small Saloon – Kes.250, Standard Saloon – Kes.500, Mid SUV – Kes.750, SUV – Kes.1,000, Luxury SUV – Kes.1,500.</p>
      </div>
    ";

// Decide which block to include
$html .= ($contract['cdw'] === "true") ? $cdwBlock : $nonCdwBlock;

$html .= "
        <br>
        <p><b>IMPORTANT</b></p>
        <p>The insurance cover(s) aforesaid is available only if the terms and conditions contained herein and in the insurance policy are complied with. Failing which, the hirer and the authorised driver shall be fully responsible for all damages and costs and shall fully indemnify the company in respect of any loss or damage suffered by the vehicle of the company or for any claims received by the company and all costs and expenses.</p>

        <p><b>PAYMENT TERMS</b></p>
        <p>7. Besides the deposit, full identifications and physical address of the hirer must be given and where required by the company an acceptable guarantee shall be provided.</p>
        <p>8. No relaxation, forbearance or indulgence by the company in enforcing any of the terms or conditions of this agreement shall prejudice or affect the rights and power of the company hereunder nor shall any waiver of any breach operate as a waiver of any subsequent or continuing breach. I hereby agree to all terms and conditions of this contract and accept full responsibility of the vehicle until it is returned to the company.</p>

        <!-- Signature + Date -->
        <table style='width:100%; margin-top:20px; font-size:13px; border-collapse:collapse;'>
          <tr>
            <td style='width:50%; padding:6px;'>Hirer's Signature:<br><img src='{$signatureUrl}' alt='Signature' style='height:60px;' /></td>
            <td style='width:50%; padding:6px;'>Date: {$contract['start_date']}</td>
          </tr>
        </table>
        </div>
    ";

// Configure Dompdf
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Stream PDF
$dompdf->stream("contract_{$id}.pdf", ["Attachment" => true]);
