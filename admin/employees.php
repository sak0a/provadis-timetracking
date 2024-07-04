<?php
include '../backend/database/Database.php';
include '../backend/database/DatabaseUtil.php';
include '../backend/database/Filter.php';

use backend\database\Database;
use backend\database\DatabaseUtil;
use backend\database\Filter;

/**
 * Initialize Database
 */
$db = Database::initDefault();
$dbUtil = new DatabaseUtil($db->getConnection());

/**
 * Initial Pagination Variables
 */
$pageNumber = 1;
$pageSize = 10;

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['ajax']) && isset($_GET['f']) && $_GET['f'] === 'get_emps') {
    try {
        echo getUsersAJAX();
    } catch (Exception $e) {
        echo '{"error": "' . $e->getMessage() . '"}';
    }
    exit();
}
/**
 * @return string
 * @throws Exception
 * Get all users via AJAX request
 */
function getUsersAJAX(): string {
    global $dbUtil, $pageSize;
    $response = '';
    // Store the request in the session if its set
    // Get the search parameters from the session
    $searchPersonalNumber = $_GET['s_pn'] ?? '';
    $searchFirstName = $_GET['s_fn'] ?? '';
    $searchLastName = $_GET['s_ln'] ?? '';
    $searchEmail = $_GET['s_email'] ?? '';
    $searchRole = $_GET['s_role'] ?? '';
    $searchEntryDate = $_GET['s_entry'] ?? '';
    $searchOrder = isset($_GET['s_order']) && $_GET['s_order'] == 'asc' ? 'asc' : 'desc';
    $pageNumber = isset($_GET['s_page']) && is_numeric($_GET['s_page']) ? intval($_GET['s_page']) : 1;

    // Initialize Filter
    $searchFilter = new Filter("");

    /**
     * Construct the filter
     */
    if (!empty($searchPersonalNumber)) {
        $searchFilter->addFilter("personal_number", '$li',  $searchPersonalNumber . "%");
    }

    if (!empty($searchFirstName)) {
        $searchFilter->addFilter("first_name", '$li', $searchFirstName . "%");
    }

    if (!empty($searchLastName)) {
        $searchFilter->addFilter("last_name", '$li',  $searchLastName . "%");
    }

    if (!empty($searchEmail)) {
        $searchFilter->addFilter("email", '$li', "%" . $searchEmail . "%");
    }

    if (!empty($searchRole)) {
        if (filter_var($searchRole, FILTER_VALIDATE_INT) !== false) {
            $searchFilter->addFilter("role_id", '$eq', $searchRole);
        } else {
            $firstMatch = $dbUtil->getRolesLike( $searchRole . "%");
            if ($firstMatch[0] !== null) {
                $searchFilter->addFilter("role_id", '$eq', $firstMatch[0]["role_id"]);
            }
        }
    }

    if (!empty($searchEntryDate)) {
        $searchFilter->addFilter("entry_date", '$li', "%" . $searchEntryDate . "%");
    }

    $userCount = $dbUtil->getUserCountByFilter($searchFilter);
    if ($userCount < $pageSize) {
        $pageNumber = 1;
    }

    $users = [];
    if ($userCount > 0) {
        $users = $dbUtil->getUsersByFilter($searchFilter, $pageNumber, $pageSize, $searchOrder);
    }
    /**
     * Calculate range for display
     * For example: 1-10 of 100
     */
    $startRange = ($pageNumber - 1) * $pageSize + 1;
    $endRange = min($startRange + $pageSize - 1, $userCount);
    /**
     * Check if start range is too high
     * For example: 111-120 of 100
     * Fix: 91-100 of 100
     */
    if ($startRange > ($userCount - 10))
        $startRange = $userCount - 10;
    /**
     * Check if start range is too low
     * For example: -10-0 of 0
     * Fix: 0-0 of 0
     */
    if ($startRange < 1)
        $startRange = 1;
    /**
     * Check if the page number is too high
     * Calculate available pages and set page number to the last page
     */
    $calculatePageNumber = round($userCount / $pageSize, 0, PHP_ROUND_HALF_DOWN);
    if ($calculatePageNumber + 1 < $pageNumber)
        $pageNumber = $calculatePageNumber;
    /**
     * Create JSON response
     */
    $responseArray = [
        "start_range" => $startRange,
        "end_range" => $endRange,
        "total_results" => $userCount,
        "page_range" => 2,
        "current_page" => $pageNumber,
        "total_pages" => ceil($userCount / $pageSize),
        "page_size" => $pageSize,
        "users" => []
    ];
    foreach ($users as $user) {
        $responseArray["users"][] = [
            "personal_number" => $user["personal_number"],
            "first_name" => $user["first_name"],
            "last_name" => $user["last_name"],
            "email" => $user["email"],
            "birthdate" => $user["birthdate"],
            "entry_date" => $user["entry_date"],
            "exit_date" => $user["exit_date"],
            "role_id" => $user["role_id"],
            "role_name" => $dbUtil->getRoleById($user["role_id"])["role_name"],
            "disability" => $user["disability"]
        ];
    }
    return json_encode($responseArray);
}
?>
<div class="employee-container">
    <!-- Benutzerübersicht -->
    <div class="employee-main-container">
        <!-- Container Header -->
        <div class="employee-header">
            <div>
                <h1 class="title">Benutzerverwaltung</h1>
                <p class="description">Verwalten Sie alle Mitarbeiter ihres Unternehmens</p>
            </div>
            <button class="add-employee-btn" onclick="document.getElementById('addUserModal').style.display='block'">
                <i class="material-icons">add</i>
                <span class="ml-1">Benutzer hinzufügen</span>
            </button>
        </div>
        <!-- Table -->
        <div class="mt-8 flow-root">
            <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="employee-table-wrapper">
                    <table>
                        <thead>
                        <tr class="table-head-search">
                            <th scope='col'>
                                <div class='search-box'>
                                    <div class='search-icon'>
                                        <svg xmlns='http://www.w3.org/2000/svg' class='h-5 w-5' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                                            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z' />
                                        </svg>
                                    </div>
                                    <input type='text' name='emp_search_pn' placeholder='Suche...' value='' />
                                </div>
                            </th>
                            <th scope='col'>
                                <div class='search-box'>
                                    <div class='search-icon'>
                                        <svg xmlns='http://www.w3.org/2000/svg' class='h-5 w-5' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                                            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z' />
                                        </svg>
                                    </div>
                                    <input type='text' name='emp_search_fn' placeholder='Suche...' value='' />
                                </div>
                            </th>
                            <th scope='col'>
                                <div class='search-box'>
                                    <div class='search-icon'>
                                        <svg xmlns='http://www.w3.org/2000/svg' class='h-5 w-5' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                                            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z' />
                                        </svg>
                                    </div>
                                  <input type='text' name='emp_search_ln' placeholder='Suche...' value='' />
                                 </div>
                            </th>
                            <th scope='col'>
                                <div class='search-box'>
                                    <div class='search-icon'>
                                        <svg xmlns='http://www.w3.org/2000/svg' class='h-5 w-5' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                                            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z' />
                                        </svg>
                                    </div>
                                  <input type='text' name='emp_search_email' placeholder='Suche...' value='' />
                                 </div>
                            </th>
                            <th scope='col'>
                                  <div class='search-box'>
                                    <div class='search-icon'>
                                        <svg xmlns='http://www.w3.org/2000/svg' class='h-5 w-5' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                                            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z' />
                                        </svg>
                                    </div>
                                  <input type='text' name='emp_search_role' placeholder='Suche...' value='' />
                                 </div>
                            </th>
                            <th scope='col'>
                                <div class='search-box'>
                                    <div class='search-icon'>
                                        <svg xmlns='http://www.w3.org/2000/svg' class='h-5 w-5' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                                            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z' />
                                        </svg>
                                    </div>
                                  <input type='text' name='emp_search_entrydate' placeholder='Suche...' value='' />
                                 </div>
                            </th>
                        </tr>
                        <tr class="table-head-titles">
                            <th scope="col">Personalnummer</th>
                            <th scope="col">Vorname</th>
                            <th scope="col">Nachname</th>
                            <th scope="col">E-Mail</th>
                            <th scope="col">Rolle</th>
                            <th scope="col">Eintrittsdatum</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y" id="tableBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- Pagination -->
        <div class="flex items-center justify-between px-2 py-2 sm:px-6">
            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                <div id="pagination_results">
                    <p class='text-sm text-gray-400'>
                        Zeige
                        <span class='font-medium start-range'>0</span>
                        bis
                        <span class='font-medium end-range'>0</span>
                        von
                        <span class='font-medium total-results'>0</span>
                        Ergebnissen
                    </p>
                </div>
                <div >
                    <nav id="pagination_nav" class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                    </nav>
                    <form id="pagination_form" class='hidden mx-4 inline-flex mt-2 text-xs'>
                        <label>
                            <input type='number' name='page' min='1' max='2'  class='ring-1 text-xs ring-inset ring-gray-300 rounded-md' placeholder='Go to page...' required>
                        </label>
                    </form>
                </div>
            </div>
        </div>
    </div>


<!-- Modal for adding a new user -->
<div id="addUserModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('addUserModal').style.display='none'">&times;</span>
        <h2>Neuen Benutzer hinzufügen</h2>
        <form id="addUserForm" method="post" action="../backend/add_user.php">
            <div class="form-group">
                <label for="personal_number">Personalnummer:</label>
                <input type="text" id="personal_number" name="personal_number" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="first_name">Vorname:</label>
                <input type="text" id="first_name" name="first_name" required>
            </div>
            <div class="form-group">
                <label for="last_name">Nachname:</label>
                <input type="text" id="last_name" name="last_name" required>
            </div>
            <div class="form-group">
                <label for="birthdate">Geburtsdatum:</label>
                <input type="date" id="birthdate" name="birthdate" required>
            </div>
            <div class="form-group">
                <label for="password">Passwort:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="role_id">Rolle:</label>
                <select id="role_id" name="role_id" required>
                    <option value="6">Admin</option>
                    <option value="7">Projektleiter</option>
                    <option value="8">Mitarbeiter</option>
                </select>
            </div>
            <button type="submit" class="button">Benutzer hinzufügen</button>
        </form>
    </div>
</div>

<!-- Modal for more details -->
<div id="modal-overlay" class="hidden" style="z-index: 98"></div>
<div id="modal" class="hidden" style="z-index: 99">
    <div class="modal-content">
        <div class="grid grid-cols-3 gap-4 h-full">
            <div class="col-span-2">
                <div class="mb-4 flex flex-wrap justify-center gap-2">
                    <button class="left-tab-button flex-grow px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded" data-tab="1">Benutzerdetails</button>
                    <button class="left-tab-button flex-grow px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded " data-tab="5">Projekte und Aufgaben</button>
                </div>
                <div class="tab-content hidden" data-content="1"></div>
                <div class="tab-content hidden" data-content="5"></div>
            </div>
            <div class="col-span-1">
                <div class="mb-4 flex flex-wrap justify-center gap-2">
                    <button class="right-tab-button flex-grow px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded" data-tab="6">1 Monat</button>
                    <button class="right-tab-button flex-grow px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded" data-tab="7">3 Monate</button>
                    <button class="right-tab-button flex-grow px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded" data-tab="8">6 Monate</button>
                    <button class="right-tab-button flex-grow px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded" data-tab="9">Gesamte Zeit</button>
                    <button class="right-tab-button flex-grow px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded" data-tab="10">Produktivität</button>
                </div>
                <div class="tab-content hidden" data-content="6"></div>
                <div class="tab-content hidden" data-content="7"></div>
                <div class="tab-content hidden" data-content="8"></div>
                <div class="tab-content hidden" data-content="9"></div>
                <div class="tab-content hidden" data-content="10"></div>
            </div>
        </div>
         <i id="close-button" class="material-icons" style="z-index: 100;">close</i>
    </div>
</div>

</div>