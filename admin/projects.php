<?php
global $projects;
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
 * Search Filters (normally from session, maybe remove later)
 */
$searchId = '';
$searchName = '';
$searchLeader = '';
$searchStartDate = '';
$searchEndDate = '';
$searchStatus = '';
$searchOrder = 'desc';

/**
 * Initial Pagination Variables
 */
$pageNumber = 1;
$pageSize = 10;
$resultCount = $dbUtil->getTotalUserCount();

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['ajax']) && isset($_GET['f']) && $_GET['f'] === 'get_projects') {
    try {
        echo getProjectsAJAX();
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
function getProjectsAJAX(): string {
    global $dbUtil, $pageSize;
    // Store the request in the session if its set
    // Get the search parameters from the session
    $searchId = $_GET['s_id'] ?? '';
    $searchName = $_GET['s_name'] ?? '';
    $searchStartDate = $_GET['s_start'] ?? '';
    $searchEndDate = $_GET['s_end'] ?? '';
    $searchStatus = $_GET['s_status'] ?? '';
    $searchPlannedTime = $_GET['s_planned_time'] ?? '';
    $searchOrder = isset($_GET['s_order']) && $_GET['s_order'] == 'asc' ? 'asc' : 'desc';
    $pageNumber = isset($_GET['s_page']) && is_numeric($_GET['s_page']) ? intval($_GET['s_page']) : 1;

    // Initialize Filter
    $searchFilter = new Filter("");

    /**
     * Construct the filter
     */


    if (!empty($searchId)) {
        $searchFilter->addFilter("project_id", '$eq',  $searchId);
    }

    if (!empty($searchName)) {
        $searchFilter->addFilter("project_name", '$li', $searchName . "%");
    }

    if (!empty($searchStartDate)) {
        $searchFilter->addFilter("start_date", '$li', "%" . $searchStartDate . "%");
    }

    if (!empty($searchEndDate)) {
        $searchFilter->addFilter("end_date", '$li', "%" . $searchEndDate . "%");
    }

    if (!empty($searchPlannedTime)) {
        $searchFilter->addFilter("planned_time", '$gt', $searchPlannedTime);
    }

    if (!empty($searchStatus)) {
        if (filter_var($searchFilter, FILTER_VALIDATE_INT) !== false) {
            $searchFilter->addFilter("status_id", '$eq', $searchStatus);
        } else {
            $firstMatch = $dbUtil->getProjectStatusLike( $searchStatus . "%");
            if ($firstMatch[0] !== null) {
                $searchFilter->addFilter("status_id", '$eq', $firstMatch[0]["status_id"]);
            }
        }
    }

    $searchFilter->addFilter('project_id', '$ne', '184');

    $projectCount = $dbUtil->getProjectCountByFilter($searchFilter);

    if ($projectCount < $pageSize) {
        $pageNumber = 1;
    }

    $projects = [];
    if ($projectCount > 0) {
        $projects = $dbUtil->getProjectsByFilter($searchFilter, $pageNumber, $pageSize, $searchOrder);
    }
    /**
     * Calculate range for display
     * For example: 1-10 of 100
     */
    $startRange = ($pageNumber - 1) * $pageSize + 1;
    $endRange = min($startRange + $pageSize - 1, $projectCount);
    /**
     * Check if start range is too high
     * For example: 111-120 of 100
     * Fix: 91-100 of 100
     */
    if ($startRange > ($projectCount - 10))
        $startRange = $projectCount - 10;
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
    $calculatePageNumber = round($projectCount / $pageSize, 0, PHP_ROUND_HALF_DOWN);
    if ($calculatePageNumber + 1 < $pageNumber)
        $pageNumber = $calculatePageNumber;
    /**
     * Create JSON response
     */
    $responseArray = [
        "start_range" => $startRange,
        "end_range" => $endRange,
        "total_results" => $projectCount,
        "page_range" => 2,
        "current_page" => $pageNumber,
        "total_pages" => ceil($projectCount / $pageSize),
        "page_size" => $pageSize,
        "projects" => []
    ];
    foreach ($projects as $project) {
        $projectStatus = $dbUtil->getProjectStatusById($project['status_id']);
        $responseArray["projects"][] = [
            "id" => $project["project_id"],
            "name" => $project["project_name"],
            "start_date" => $project["start_date"],
            "end_date" => $project["end_date"],
            "status_id" => $projectStatus['status_id'],
            "status_name" => $projectStatus["status_name"],
            "planned_time" => $project["planned_time"]
        ];
    }
    return json_encode($responseArray);
}
?>
<div class="projects-container">
    <!-- Benutzerübersicht -->
    <div class="projects-main-container">
        <!-- Container Header -->
        <div class="projects-header">
            <div class="">
                <h1 class="title">Projektverwaltung</h1>
                <p class="description">Verwalten Sie alle Projekte ihres Unternehmens</p>
            </div>
            <button class="add-project-btn" onclick="document.getElementById('addProjectModal').style.display='block'">
                <i class="material-icons">add</i>
                <span class="ml-1">Projekt hinzufügen</span>
            </button>
        </div>
        <!-- Table -->
        <div class="mt-8 flow-root">
            <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="projects-table-wrapper">
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
                                    <input type='text' name='project_search_id' placeholder='Suche...' value='' />
                                </div>
                            </th>
                            <th scope='col'>
                                <div class='search-box'>
                                    <div class='search-icon'>
                                        <svg xmlns='http://www.w3.org/2000/svg' class='h-5 w-5' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                                            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z' />
                                        </svg>
                                    </div>
                                    <input type='text' name='project_search_name' placeholder='Suche...' value='' />
                                </div>
                            </th>
                            <th scope='col'>
                                <div class='search-box'>
                                    <div class='search-icon'>
                                        <svg xmlns='http://www.w3.org/2000/svg' class='h-5 w-5' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                                            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z' />
                                        </svg>
                                    </div>
                                  <input type='text' name='project_search_planned_time' placeholder='Suche...' value='' />
                                 </div>
                            </th>
                            <th scope='col'>
                                <div class='search-box'>
                                    <div class='search-icon'>
                                        <svg xmlns='http://www.w3.org/2000/svg' class='h-5 w-5' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                                            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z' />
                                        </svg>
                                    </div>
                                  <input type='text' name='project_search_start_date' placeholder='Suche...' value='' />
                                 </div>
                            </th>
                            <th scope='col'>
                                <div class='search-box'>
                                    <div class='search-icon'>
                                        <svg xmlns='http://www.w3.org/2000/svg' class='h-5 w-5' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                                            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z' />
                                        </svg>
                                    </div>
                                  <input type='text' name='project_search_end_date' placeholder='Suche...' value='' />
                                 </div>
                            </th>
                            <th scope='col'>
                                <div class='search-box'>
                                    <div class='search-icon'>
                                        <svg xmlns='http://www.w3.org/2000/svg' class='h-5 w-5' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                                            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z' />
                                        </svg>
                                    </div>
                                  <input type='text' name='project_search_status' placeholder='Suche...' value='' />
                                 </div>
                            </th>
                        </tr>
                        <tr class="table-head-titles">
                            <th scope="col">ID</th>
                            <th scope="col">Name</th>
                            <th scope="col">Geplante Stunden</th>
                            <th scope="col">Start Datum</th>
                            <th scope="col">End Datum</th>
                            <th scope="col">Status</th>
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


    <div id="addProjectModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('addProjectModal').style.display='none'">Close &times;</span>
            <h2>Neues Projekt hinzufügen</h2>
            <form id="addProjectForm" method="post" action="../backend/add_project.php">
                <div class="form-group">
                    <label for="project_name">Projektname</label> <!--es wird in die tabelle Projects geschrieben-->
                    <input type="text" id="project_name" name="project_name" required>
                </div>
                <div class="form-group">
                    <label for="start_date">Startdatum</label> <!--es wird in die tabelle Projects geschrieben-->
                    <input type="date" id="start_date" name="start_date" required>
                </div>
                <div class="form-group">
                    <label for="status_id">Status</label> <!--es wird in die tabelle Projects geschrieben-->
                    <select id="status_id" name="status_id" required>
                        <option value="9">Abgeschlossen</option>
                        <option value="10">In Bearbeitung</option>
                        <option value="12">Pausiert</option>
                        <option value="11">Abgebrochen</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="department_head">Verantwortlicher</label> <!--es wird in die tabelle UserRoles (user_id)(project_id)(role_id) geschrieben-->
                    <select id="department_head" name="department_head" required>
                        <option value="1">Alex</option>
                        <option value="2">maria</option>
                        <option value="3">anton</option>
                        <option value="4">Laurin'o</option>
                    </select>
                </div>
                <button type="submit" class="button">Projekt hinzufügen</button>
            </form>
        </div>
    </div>

    <!-- Modal for more details -->
    <div id="moreDetails" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">Close &times;</span>
            <div id="projectDetailsContent"></div>
        </div>
    </div>

</div>