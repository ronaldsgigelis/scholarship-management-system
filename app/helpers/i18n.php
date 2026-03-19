<?php

declare(strict_types=1);

function supported_languages(): array
{
    return ['en', 'lv'];
}

function current_language(): string
{
    $language = $_SESSION['language'] ?? 'en';

    return in_array($language, supported_languages(), true) ? $language : 'en';
}

function set_language_from_request(): void
{
    $requestedLanguage = $_GET['lang'] ?? null;

    if (is_string($requestedLanguage) && in_array($requestedLanguage, supported_languages(), true)) {
        $_SESSION['language'] = $requestedLanguage;
    }
}

function translations(): array
{
    static $cache = [];

    $language = current_language();

    if (! isset($cache[$language])) {
        $filePath = BASE_PATH . '/lang/' . $language . '.php';
        $cache[$language] = file_exists($filePath) ? require $filePath : [];
    }

    return $cache[$language];
}

function t(string $key, array $replace = []): string
{
    $text = translations()[$key] ?? $key;

    foreach ($replace as $name => $value) {
        $text = str_replace(':' . $name, (string) $value, $text);
    }

    return $text;
}

function language_url(string $language): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $query = $_GET;
    $query['lang'] = $language;

    return $path . '?' . http_build_query($query);
}

function render_language_switch(): string
{
    $currentLanguage = current_language();
    $languages = [
        'en' => 'EN',
        'lv' => 'LV',
    ];

    $items = [];

    foreach ($languages as $code => $label) {
        $className = $currentLanguage === $code ? 'fw-bold text-decoration-underline' : '';
        $items[] = sprintf(
            '<a href="%s" class="small text-decoration-none %s">%s</a>',
            htmlspecialchars(language_url($code), ENT_QUOTES, 'UTF-8'),
            $className,
            htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
        );
    }

    return '<div class="d-flex gap-2 align-items-center"><span class="small text-muted">' .
        htmlspecialchars(t('common.language'), ENT_QUOTES, 'UTF-8') .
        '</span>' . implode('<span class="text-muted">|</span>', $items) . '</div>';
}

function translate_output(string $content): string
{
    if (current_language() !== 'lv') {
        return $content;
    }

    $replacements = [
        'Scholarship Management System' => 'Stipendiju pārvaldības sistēma',
        'Manage students, subjects, periods, and scholarship results in one system.' => 'Pārvaldiet studentus, priekšmetus, periodus un stipendiju rezultātus vienā sistēmā.',
        'Use the modules below to manage groups, students, subjects, stipend periods, and saved scholarship results.' => 'Izmantojiet zemāk esošos moduļus, lai pārvaldītu grupas, studentus, priekšmetus, stipendiju periodus un saglabātos stipendiju rezultātus.',
        'Manage study groups.' => 'Pārvaldiet mācību grupas.',
        'Manage student records.' => 'Pārvaldiet studentu ierakstus.',
        'Manage subjects and categories.' => 'Pārvaldiet priekšmetus un kategorijas.',
        'Link subjects to groups.' => 'Piesaistiet priekšmetus grupām.',
        'Manage stipend periods.' => 'Pārvaldiet stipendiju periodus.',
        'Enter grades and absences.' => 'Ievadiet atzīmes un kavējumus.',
        'Search saved results.' => 'Meklējiet saglabātos rezultātus.',
        'Groups' => 'Grupas',
        'Students' => 'Studenti',
        'Subjects' => 'Priekšmeti',
        'Group Subjects' => 'Grupu priekšmeti',
        'Stipend Periods' => 'Stipendiju periodi',
        'Stipend Entry' => 'Stipendiju ievade',
        'Search / History' => 'Meklēšana / Vēsture',
        'Search History' => 'Meklēšana / Vēsture',
        'Stipend Result Detail' => 'Stipendijas rezultāta detaļas',
        'Create Group' => 'Izveidot grupu',
        'Edit Group' => 'Rediģēt grupu',
        'Create Student' => 'Izveidot studentu',
        'Edit Student' => 'Rediģēt studentu',
        'Create Subject' => 'Izveidot priekšmetu',
        'Edit Subject' => 'Rediģēt priekšmetu',
        'Create Assignment' => 'Izveidot piesaisti',
        'Create Stipend Period' => 'Izveidot stipendiju periodu',
        'Edit Stipend Period' => 'Rediģēt stipendiju periodu',
        'Manage study groups for the scholarship system.' => 'Pārvaldiet mācību grupas stipendiju sistēmā.',
        'Manage students with first name, last name, and personal code.' => 'Pārvaldiet studentus ar vārdu, uzvārdu un personas kodu.',
        'Manage subject names and category types.' => 'Pārvaldiet priekšmetu nosaukumus un kategoriju tipus.',
        'Manage documented stipend periods by year, period, and period group.' => 'Pārvaldiet dokumentācijai atbilstošus stipendiju periodus pēc gada, perioda un perioda grupas.',
        'Manage subject assignments for groups.' => 'Pārvaldiet priekšmetu piesaistes grupām.',
        'Load students and subjects for the stipend entry form.' => 'Ielādējiet studentus un priekšmetus stipendiju ievades formai.',
        'Load students and subjects for the future stipend form.' => 'Ielādējiet studentus un priekšmetus stipendiju ievades formai.',
        'Search saved stipend results by year, period, group, or student.' => 'Meklējiet saglabātos stipendiju rezultātus pēc gada, perioda, grupas vai studenta.',
        'Detailed view of one saved stipend result.' => 'Detalizēts viena saglabāta stipendijas rezultāta skats.',
        'Add a student with first name, last name, and personal code.' => 'Pievienojiet studentu ar vārdu, uzvārdu un personas kodu.',
        'Update student information, personal code, and assigned group.' => 'Atjauniniet studenta informāciju, personas kodu un piešķirto grupu.',
        'Update the selected group name.' => 'Atjauniniet izvēlēto grupas nosaukumu.',
        'Update the selected subject and category type.' => 'Atjauniniet izvēlēto priekšmetu un kategorijas tipu.',
        'Add a year, period, and period group combination.' => 'Pievienojiet gada, perioda un perioda grupas kombināciju.',
        'Update the selected year, period, and period group.' => 'Atjauniniet izvēlēto gadu, periodu un perioda grupu.',
        'Assign a subject to a group.' => 'Piesaistiet priekšmetu grupai.',
        'Home' => 'Sākums',
        'Back to Groups' => 'Atpakaļ uz grupām',
        'Back to Students' => 'Atpakaļ uz studentiem',
        'Back to Subjects' => 'Atpakaļ uz priekšmetiem',
        'Back to Assignments' => 'Atpakaļ uz piesaistēm',
        'Back to Periods' => 'Atpakaļ uz periodiem',
        'Back to Search' => 'Atpakaļ uz meklēšanu',
        'Search' => 'Meklēt',
        'View' => 'Skatīt',
        'Edit' => 'Rediģēt',
        'Delete' => 'Dzēst',
        'Cancel' => 'Atcelt',
        'Save Group' => 'Saglabāt grupu',
        'Update Group' => 'Atjaunināt grupu',
        'Save Student' => 'Saglabāt studentu',
        'Update Student' => 'Atjaunināt studentu',
        'Save Subject' => 'Saglabāt priekšmetu',
        'Update Subject' => 'Atjaunināt priekšmetu',
        'Save Assignment' => 'Saglabāt piesaisti',
        'Save Period' => 'Saglabāt periodu',
        'Update Period' => 'Atjaunināt periodu',
        'Save Entry Data' => 'Saglabāt ievades datus',
        'Clear Filters' => 'Notīrīt filtrus',
        'Filters' => 'Filtri',
        'Use one or more filters to narrow the saved stipend records.' => 'Izmantojiet vienu vai vairākus filtrus, lai sašaurinātu saglabātos stipendiju ierakstus.',
        'Selection' => 'Atlase',
        'Choose a stipend period and a group before loading student entry rows.' => 'Pirms studentu rindu ielādes izvēlieties stipendiju periodu un grupu.',
        'Results' => 'Rezultāti',
        'Student Information' => 'Studenta informācija',
        'Stipend Summary' => 'Stipendijas kopsavilkums',
        'Selected Data' => 'Izvēlētie dati',
        'Assigned Subjects' => 'Piesaistītie priekšmeti',
        'Student Input' => 'Studentu ievade',
        'Grades by Subject' => 'Atzīmes pa priekšmetiem',
        'ID' => 'ID',
        'Group Name' => 'Grupas nosaukums',
        'First Name' => 'Vārds',
        'Last Name' => 'Uzvārds',
        'Personal Code' => 'Personas kods',
        'Subject Name' => 'Priekšmeta nosaukums',
        'Category Type' => 'Kategorijas tips',
        'Stipend Period' => 'Stipendijas periods',
        'Group' => 'Grupa',
        'Student' => 'Students',
        'Year' => 'Gads',
        'Period / Month' => 'Periods / Mēnesis',
        'Period' => 'Periods',
        'Period Group' => 'Perioda grupa',
        'Absence Count' => 'Kavējumu skaits',
        'Abs.' => 'Kav.',
        'Activity Bonus' => 'Aktivitātes bonuss',
        'Bonus' => 'Bonuss',
        'Average Grade' => 'Vidējā atzīme',
        'Avg.' => 'Vid.',
        'Failed Subjects' => 'Nesekmīgie priekšmeti',
        'Fails' => 'Nes.',
        'Base Stipend' => 'Pamata stipendija',
        'Base' => 'Pamats',
        'Total Stipend' => 'Kopējā stipendija',
        'Total' => 'Kopā',
        'Grade' => 'Atzīme',
        'Created At' => 'Izveidots',
        'Actions' => 'Darbības',
        'No groups found.' => 'Grupas nav atrastas.',
        'No students found.' => 'Studenti nav atrasti.',
        'No subjects found.' => 'Priekšmeti nav atrasti.',
        'No assignments found.' => 'Piesaistes nav atrastas.',
        'No stipend periods found.' => 'Stipendiju periodi nav atrasti.',
        'No stipend periods found. Create stipend periods first.' => 'Stipendiju periodi nav atrasti. Vispirms izveidojiet stipendiju periodus.',
        'No groups found. Create groups first.' => 'Grupas nav atrastas. Vispirms izveidojiet grupas.',
        'No results found for the selected filters.' => 'Atlasītajiem filtriem rezultāti netika atrasti.',
        'Apply filters to view saved stipend results.' => 'Lai redzētu saglabātos stipendiju rezultātus, izmantojiet filtrus.',
        'This group has no students.' => 'Šajā grupā nav studentu.',
        'This group has no assigned subjects.' => 'Šai grupai nav piesaistītu priekšmetu.',
        'Groups must be created first before adding students.' => 'Pirms studentu pievienošanas vispirms jāizveido grupas.',
        'Groups must exist before updating a student.' => 'Lai rediģētu studentu, ir jābūt izveidotām grupām.',
        'Groups must be created first before making assignments.' => 'Pirms piesaistu veidošanas vispirms jāizveido grupas.',
        'Subjects must be created first before making assignments.' => 'Pirms piesaistu veidošanas vispirms jāizveido priekšmeti.',
        'Please select both stipend period and group.' => 'Lūdzu, izvēlieties gan stipendijas periodu, gan grupu.',
        'Selected stipend period or group was not found.' => 'Izvēlētais stipendijas periods vai grupa netika atrasta.',
        'Scholarship is calculated only when at least one grade is entered for the student. Rows without grades show neutral values.' => 'Stipendija tiek aprēķināta tikai tad, ja studentam ir ievadīta vismaz viena atzīme. Rindas bez atzīmēm rāda neitrālas vērtības.',
        'No grades were entered for this stipend result, so scholarship values were not calculated.' => 'Šim stipendijas rezultātam atzīmes netika ievadītas, tāpēc stipendijas vērtības netika aprēķinātas.',
        'Activity bonus is stored for a 6-month period starting from the selected stipend month.' => 'Aktivitātes bonuss tiek glabāts 6 mēnešu periodam, sākot ar izvēlēto stipendijas mēnesi.',
        'All years' => 'Visi gadi',
        'All periods' => 'Visi periodi',
        'All period groups' => 'Visas periodu grupas',
        'All groups' => 'Visas grupas',
        'All students' => 'Visi studenti',
        'Student Name Search' => 'Studenta vārda meklēšana',
        'Enter first name, last name, or full name' => 'Ievadiet vārdu, uzvārdu vai pilnu vārdu',
        'Select stipend period' => 'Izvēlieties stipendiju periodu',
        'Select group' => 'Izvēlieties grupu',
        'Select a group' => 'Izvēlieties grupu',
        'Select a subject' => 'Izvēlieties priekšmetu',
        'Are you sure you want to delete this group?' => 'Vai tiešām vēlaties dzēst šo grupu?',
        'Are you sure you want to delete this student?' => 'Vai tiešām vēlaties dzēst šo studentu?',
        'Are you sure you want to delete this subject?' => 'Vai tiešām vēlaties dzēst šo priekšmetu?',
        'Are you sure you want to delete this assignment?' => 'Vai tiešām vēlaties dzēst šo piesaisti?',
        'Are you sure you want to delete this stipend period?' => 'Vai tiešām vēlaties dzēst šo stipendijas periodu?',
    ];

    return strtr($content, $replacements);
}