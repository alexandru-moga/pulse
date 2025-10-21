<?php
require_once __DIR__ . '/../core/init.php';
checkLoggedIn();
checkRole(['Leader', 'Co-leader']);

global $db;

$pageTitle = "Import Daydream Participants";
include __DIR__ . '/components/dashboard-header.php';

$importResults = [];
$totalProcessed = 0;
$newUsers = 0;
$existingUsers = 0;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import'])) {
    // Get Daydream event ID
    $daydreamEventId = 9; // From the database
    
    // CSV data from the file
    $csvData = <<<CSV
First Name,Last Name,Email,Phone Number,Volunteer
Astratinei,Ovidiu-Gabriel,oviastra6@gmail.com,+40 745 654 203,FALSE
Ayhan-Alex,Mahmut,susximposter96@gmail.com,+40 721 753 151,FALSE
Berehoi-Croitoru,David,berehoidavid24@gmail.com,+40 773 746 129,FALSE
Bociu,Luca,Suleimanmacnificul228@gmail.com,+40 770 686 061,FALSE
Carabus,Darius,carabusluci50@gmail.com,+40 733 969 672,FALSE
Cirstea,Andrei,averagebusser@gmail.com,+40 770 263 036,FALSE
Crăciun,Eduard,eduard.craciun@cthc.ro,+40 737 183 842,FALSE
Crăciun,Marco,marco.craciun@cthc.ro,+40 737 185 112,FALSE
Croitoru,Costel,croitoru.costel09@gmail.com,+40 770 424 490,TRUE
Croitoru,Lenuta,croitoru.elena09@gmail.com,+40 740 881 526,TRUE
Cutlac,David,davidcutlac8@gmail.com,+40 759 493 891,FALSE
Damian,Andreea,damian.andreea.valentina@gmail.com,+407 242 607 950,FALSE
Damian,Karina,biancadamian5@gmail.com,+40 723 585 926,FALSE
Dobra,Sara Coca,sara.dobra@cthc.ro,+40 770 740 891,FALSE
Faur,Rares,dreamrarescool@gmail.com,+40 734 034 051,FALSE
Gal,Jazmin Eszter,eszjaz@gmail.com,+40 722 590 739,FALSE
Gheorghescu,Robest Cristian,liliana2006tritoiu@yahoo.com,+40 753 055 556,FALSE
Ianisia,Radu Basarab,ancaradubasarab@yahoo.com,+40 726 263 848,FALSE
Icobescu,Aldana,aldana.icobescu@gmail.com,+40 770 531 550,FALSE
Laza,David,mihaelalaza@ymail.com,+40 762 964 584,FALSE
Liță,Gabriel Ionuț,gabriel.lita@cthc.ro,+40 733 202 639,FALSE
Manolescu,Alexia,alexiamanolescu2006@gmail.com,+40 770 724 809,FALSE
Mihai,Drăghici,mihaidraghici023@gmail.com,+40 739 047 914,FALSE
Muresan,Denis,denis.muresan@cthc.ro,+40 728 555 301,FALSE
Musat,Tudor,musat.tudor37@gmail.com,+40 740 240 087,FALSE
Nițulescu,Denis,denisnitulescu2000@gmail.com,+40 771 350 827,FALSE
Nueleanu,Daniel,nueleanudaniel2008@gmail.com,+40 732 304 437,FALSE
Popa,Anamaria,anapopa2222@gmail.com,+40 733 243 083,FALSE
Remescu,Maria,mariaremescu@gmail.com,+40 774 918 145,FALSE
Stefan,Cretu,crstefan2018@gmail.com,+40 771 502 288,FALSE
Tania,Titirigă,tania.titiriga05@e-uvt.ro,+40 728 602 031,TRUE
Theodor,Marinescu,marinescu.theodor9@gmail.com,+40 735 963 844,FALSE
Țintean,Mihai Ioan,mihaitintean@gmail.com,+40 787 668 047,FALSE
Tudor,Negru,tudorblack10@gmail.com,+40 734 781 236,FALSE
Alexandra Maria,Sunea,mayothelemonlord@gmail.com,+40 740 074 540,FALSE
Andrei,Maier,andy22082007@gmail.com,+40 787 547 711,FALSE
Andrica,Alexandru,cristina.andrica@htdesign.ro,+40 744 701 160,FALSE
Ayten,Mahmut,aytenutza@gmail.com,+40 742 387 431,TRUE
Bachici,Mateo,me387343@gmail.com,+40 742 395 701,FALSE
Barzan,Maya Rebeca,horvat_ramona@yahoo.com,+40 748 136 206,FALSE
Becheru,Marco,marcobecheru10@gmail.com,+40 771 390 188,FALSE
Berehoi Croitoru,Cristian Roland,berehoicristian@gmail.com,+40 740 195 273,TRUE
Bimbirica,Andra,andrabimbirica4@gmail.com,+40 751 976 587,TRUE
Birnea,Ianis,byan161020@gmail.com,+40 736 439 784,FALSE
Brașoveanu,Cristian,thedark0015@gmail.com,+40 753 612 948,FALSE
Bujnita,Razvan,bujnitarazvan@gmail.com,+40 770 753 391,FALSE
Buznea,Luca,lucabuznea1@gmail.com,+40 734 049 020,FALSE
Buznea,Rareș,buzneararesioan2008@gmail.com,+40 770 688 312,FALSE
Ciaușu,Alex,dumitrescualex9@gmail.com,+40 736 801 643,FALSE
Ciui,Lucas,lucasciui22@gmail.com,+40 774 546 979,FALSE
Corcau,Andrei,andrei.corcau@gmail.com,+40 774 675 824,FALSE
Crutan,Clara,cclara731@yahoo.com,+40 734 383 992,FALSE
Damian,Ioana,ioanadamiana@gmail.com,+40 773 371 181,FALSE
Dan-Cristian,Sima,dansima.cristian@gmail.com,+40 765 898 333,FALSE
Daniel,Bucur,db331171@gmail.com,+40 766 434 026,FALSE
Diaconu,Matei,mateidiaconu2012@gmail.com,+40 723 338 935,FALSE
Elias,Bota,elias.bota123@gmail.com,+40 799 831 968,TRUE
Faur,Daria,skibidushka@gmail.com,+40 736 814 578,FALSE
Fota,Dan Cristian,dancristianfota@gmail.com,+40 739 610 648,TRUE
Gangu,Rareș Andrei,gangurares11@gmail.com,+40 768 709 119,FALSE
Gruescu,Ana,anagruescu28@gmail.com,+40 729 568 120,FALSE
Guga,Vlad,vlad.guga@cthc.ro,+40 773 399 471,FALSE
Haicu,Mihnea,mihneah08@gmail.com,+40 770 526 692,FALSE
Iovita,Ștefan,stefaniov2008@hotmail.com,+40 732 189 843,FALSE
Joita,Alexandru,andrujoita@gmail.com,+40 762 436 042,FALSE
Laura,Berehoi Croitoru,laurycm02@gmail.com,+40 723 727 832,TRUE
Luca-Roberto,Al-Nicoli,al.nicoli.luca06@gmail.com,+40 723 977 694,TRUE
Margine,David,david.pungathenoob@gmail.com,+40 766 468 837,FALSE
Marin,Bogdan Constantin,bogdanmarin2090@gmail.com,+40 772 241 978,FALSE
Mark,Dragotă,mark.dragota05@e-uvt.ro,+40 784 683 688,TRUE
Mitoiu,Bogdan-Petru,bogdan.mitoiu05@e-uvt.ro,+40 721 549 492,TRUE
Miuță,Constantin Marian,marian_miuta@yahoo.com,+40 728 095 620,FALSE
Mociran,Ioana-Nicoleta,mociranioananico@gmail.com,+40 749 873 169,TRUE
Moga,Alexandru Mihai,alexandru.moga08@gmail.com,+40 773 895 241,TRUE
Moldovan,Robert,robert.moldovan05@yahoo.com,+40 757 030 772,FALSE
Molete,Eduard,yoeduard135@gmail.com,+40 742 276 349,FALSE
Morărașu,Vlad,vlad.morarasu23@gmail.com,+40 770 496 047,FALSE
Mustea,Teodora-Maria,teodora.maria.mustea@gmail.com,+40 727 380 130,FALSE
Nistor,David Traian,david.nistor@cthc.ro,+40 774 098 355,FALSE
Nitulescu,Rafael,rafaelnitulescu2000@gmail.com,+40 723 810 384,TRUE
Pani,Maria,pani.maria25@gmail.com,+40 734 419 693,FALSE
Pantalir,Ștefan,stefanpantalir@yahoo.com,+40 775 537 826,FALSE
Patrutescu,Alexandra Gabriela,oana.patrutescu@yahoo.com,+40 740 150 491,FALSE
Păun,Ayanna Karina Gabriela,ayanna.paun1966@icloud.com,+40 729 318 189,FALSE
Petre,Andrei Tudor,andrei.tudor.petre@gmail.com,+40 723 450 460,FALSE
Petruț,Florina Cristina,fcpetrut2000@gmail.com,+40 736 536 327,FALSE
Pirvu,Nikolas,pirvunikolas9@gmail.com,+40 799 346 055,FALSE
Pop,Simion-Mario,popmario112@gmail.com,+40 787 317 720,FALSE
Racoveanu,Alexandru,alexandruracoveanu06@gmail.com,+40 746 124 650,FALSE
Rafailă,Beniamin,narutogxyt@gmail.com,+40 774 484 133,TRUE
Raul,Berinde,rberinde811@gmail.com,+40 738 444 401,FALSE
Raul,Colțiș,coltis.raul@gmail.com,+40 769 512 872,FALSE
Remescu,Tudor Florian,remescutudor@gmail.com,+40 774 971 658,FALSE
Șandor,Melissa,sandormelissa93@gmail.com,+40 765 998 467,FALSE
Sasha,Makamul,velocityvortex72@gmail.com,+40 737 519 959,FALSE
Serengau,Valentin,valentinserengau@gmail.com,+40 771 077 360,FALSE
Stan,Andrada,andrada.stan@cthc.ro,+40 775 211 560,FALSE
Stancu,Sara,saramaria29035@gmail.com,+40 737 221 125,FALSE
Stanjic,Bogdan,bstanjic4@gmail.com,+40 730 518 029,FALSE
Stefaniga,Sebastian-Aurelian,sebastian.stefaniga@e-uvt.ro,+40 762 695 901,TRUE
Suciu,Darius,darius.suciu@cthc.ro,+40 745 823 885,FALSE
Tilca,Naomi,tilcamichellenaomi@gmail.com,+40 771 459 629,FALSE
Timothy,Buium,timotybuium8@gmail.com,+40 407 343 839,FALSE
Toader,Teo,tdrndr369@gmail.com,+40 739 537 380,FALSE
Toma,Ryan-Andrei-Valentin,ravtoma@gmail.com,+40 722 603 231,FALSE
Trifan,Giulia,giuliabtsarmy07@gmail.com,+40 771 635 507,TRUE
Tufiș,Răzvan,razvan.tufis10@gmail.com,+40 722 660 422,FALSE
Turculeț,Alexandru,alexandru.turculet06@e-uvt.ro,+40 771 093 247,TRUE
Ungureanu,Albert Rafael,rafael.ungureanu256@gmail.com,+40 764 653 426,FALSE
Văcaru,Alexandru,alexcrisv09@gmail.com,+40 771 519 887,FALSE
Varatuceanu,Elisabeta,elisucavaratuceanu@gmail.com,+40 771 732 940,FALSE
Varlan,Andrei-Vlad,varlanandrei241@gmail.com,+40 741 780 351,TRUE
Varnavas,Ioannis,ioannis.varnavas2010@gmail.com,+40 749 696 731,FALSE
Vătămanu,Robert,robert.vatamanu8@gmail.com,+40 771 363 841,FALSE
Vichland,Cristina,cristina.vichland00@gmail.com,+40 752 706 799,FALSE
Vintila,Robert,robert.vintila@cthc.ro,+40 763 546 557,FALSE
Vlad,Mihai,mihai.andrei.vlad@gmail.com,+40 726 943 051,FALSE
Zicoane,Daiana Tamara,zicoanedaianatamara@gmail.com,+40 767 696 612,FALSE
CSV;

    $lines = explode("\n", $csvData);
    array_shift($lines); // Remove header
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        $parts = str_getcsv($line);
        if (count($parts) < 5) continue;
        
        $firstName = trim($parts[0]);
        $lastName = trim($parts[1]);
        $email = trim(strtolower($parts[2]));
        $phone = trim(str_replace(' ', '', $parts[3])); // Remove spaces from phone
        $isVolunteer = strtoupper(trim($parts[4])) === 'TRUE';
        
        $totalProcessed++;
        
        // Check if user already exists
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $existingUser = $stmt->fetch();
        
        if ($existingUser) {
            // User exists, just add attendance
            $userId = $existingUser['id'];
            $existingUsers++;
            
            // Check if already marked as attending
            $checkStmt = $db->prepare("SELECT id FROM event_attendance WHERE event_id = ? AND user_id = ?");
            $checkStmt->execute([$daydreamEventId, $userId]);
            
            if (!$checkStmt->fetch()) {
                // Add attendance record (only for non-volunteers)
                if (!$isVolunteer) {
                    $insertStmt = $db->prepare("INSERT INTO event_attendance (event_id, user_id, status) VALUES (?, ?, 'going')");
                    $insertStmt->execute([$daydreamEventId, $userId]);
                }
            }
            
            $importResults[] = [
                'name' => $firstName . ' ' . $lastName,
                'email' => $email,
                'status' => 'existing',
                'volunteer' => $isVolunteer
            ];
        } else {
            // Create new user
            try {
                // Extract country code and phone number
                $countryCode = '+40';
                $phoneNumber = $phone;
                if (str_starts_with($phone, '+')) {
                    $countryCode = substr($phone, 0, 3);
                    $phoneNumber = substr($phone, 3);
                }
                
                $insertUserStmt = $db->prepare("INSERT INTO users 
                    (first_name, last_name, email, phone, country_code, role, active_member) 
                    VALUES (?, ?, ?, ?, ?, 'Guest', 1)");
                $insertUserStmt->execute([$firstName, $lastName, $email, $phoneNumber, $countryCode]);
                
                $userId = $db->lastInsertId();
                $newUsers++;
                
                // Add attendance record (only for non-volunteers)
                if (!$isVolunteer) {
                    $insertStmt = $db->prepare("INSERT INTO event_attendance (event_id, user_id, status) VALUES (?, ?, 'going')");
                    $insertStmt->execute([$daydreamEventId, $userId]);
                }
                
                $importResults[] = [
                    'name' => $firstName . ' ' . $lastName,
                    'email' => $email,
                    'status' => 'created',
                    'volunteer' => $isVolunteer
                ];
            } catch (Exception $e) {
                $errors[] = "Error creating user {$firstName} {$lastName} ({$email}): " . $e->getMessage();
            }
        }
    }
}
?>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Import Daydream Participants</h2>
                <p class="text-gray-600 mt-1">Import participants from CSV and mark them as attending the Daydream event</p>
            </div>
            <a href="<?= $settings['site_url'] ?>/dashboard/events.php"
                class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Events
            </a>
        </div>
    </div>

    <?php if (!empty($importResults)): ?>
    <!-- Import Results -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Import Results</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-blue-50 rounded-lg p-4">
                <div class="text-sm font-medium text-blue-800">Total Processed</div>
                <div class="text-2xl font-bold text-blue-900"><?= $totalProcessed ?></div>
            </div>
            <div class="bg-green-50 rounded-lg p-4">
                <div class="text-sm font-medium text-green-800">New Users Created</div>
                <div class="text-2xl font-bold text-green-900"><?= $newUsers ?></div>
            </div>
            <div class="bg-yellow-50 rounded-lg p-4">
                <div class="text-sm font-medium text-yellow-800">Existing Users</div>
                <div class="text-2xl font-bold text-yellow-900"><?= $existingUsers ?></div>
            </div>
        </div>

        <?php if (!empty($errors)): ?>
        <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-4">
            <h4 class="text-sm font-medium text-red-800 mb-2">Errors:</h4>
            <ul class="text-sm text-red-700 list-disc list-inside">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($importResults as $result): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($result['name']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?= htmlspecialchars($result['email']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($result['status'] === 'created'): ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    New User Created
                                </span>
                            <?php else: ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Existing User
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($result['volunteer']): ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                    Volunteer
                                </span>
                            <?php else: ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    Participant
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php else: ?>
    <!-- Import Form -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Import Information</h3>
            <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                <p class="text-sm text-blue-800">
                    This will import all participants from the Daydream CSV file:
                </p>
                <ul class="list-disc list-inside text-sm text-blue-700 mt-2 space-y-1">
                    <li>New users will be created as <strong>Guest</strong> accounts</li>
                    <li>Existing users (matched by email) will be identified</li>
                    <li><strong>Participants</strong> (non-volunteers) will be marked as attending Daydream event</li>
                    <li><strong>Volunteers</strong> will only have accounts created, not marked as participants</li>
                    <li>Phone numbers will be properly formatted with country codes</li>
                </ul>
            </div>
        </div>

        <form method="post">
            <button type="submit" name="import"
                class="inline-flex items-center px-6 py-3 border border-transparent shadow-sm text-base font-medium rounded-md text-white bg-primary hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                </svg>
                Start Import
            </button>
        </form>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/components/dashboard-footer.php'; ?>
