<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$user = getCurrentUser();

// Get recent API fetches
$conn = getDBConnection();
$recent_apis = $conn->query("SELECT * FROM api_endpoints WHERE user_id = {$_SESSION['user_id']} ORDER BY created_at DESC LIMIT 5");
$total_records = $conn->query("SELECT COUNT(*) as count FROM nyc_collisions")->fetch_assoc()['count'];
$conn->close();

include 'includes/header.php';
?>

<style>
    .api-input-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
        color: white;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    }
    
    .api-input-section h3 {
        margin-bottom: 0.5rem;
        font-size: 1.3rem;
    }
    
    .api-input-section p {
        margin-bottom: 1.5rem;
        opacity: 0.9;
        font-size: 0.9rem;
    }
    
    .api-input-row {
        display: flex;
        gap: 1rem;
        align-items: flex-end;
    }
    
    .api-url-input {
        flex: 1;
        padding: 1rem;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 10px;
        font-size: 0.95rem;
        background: rgba(255, 255, 255, 0.1);
        color: white;
        transition: all 0.3s;
    }
    
    .api-url-input::placeholder {
        color: rgba(255, 255, 255, 0.6);
    }
    
    .api-url-input:focus {
        outline: none;
        border-color: white;
        background: rgba(255, 255, 255, 0.2);
    }
    
    .btn-fetch {
        background: white;
        color: #667eea;
        border: none;
        padding: 1rem 2rem;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        white-space: nowrap;
    }
    
    .btn-fetch:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
    }
    
    .btn-fetch:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }
    
    .recent-apis {
        margin-top: 1rem;
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        align-items: center;
    }
    
    .api-tag {
        background: rgba(255, 255, 255, 0.2);
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.8rem;
        cursor: pointer;
        transition: all 0.3s;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }
    
    .api-tag:hover {
        background: rgba(255, 255, 255, 0.3);
    }
    
    .status-message {
        padding: 1rem;
        border-radius: 8px;
        margin-top: 1rem;
        display: none;
    }
    
    .status-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
        display: block;
    }
    
    .status-error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
        display: block;
    }
    
    .status-loading {
        background: #d1ecf1;
        color: #0c5460;
        border: 1px solid #bee5eb;
        display: block;
    }
    
    .data-stats {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
    }
    
    .stat-badge {
        background: #f8f9fa;
        padding: 0.75rem 1.5rem;
        border-radius: 10px;
        border: 1px solid #e0e0e0;
    }
    
    .stat-badge .label {
        font-size: 0.8rem;
        color: #666;
        text-transform: uppercase;
    }
    
    .stat-badge .value {
        font-size: 1.5rem;
        font-weight: bold;
        color: #333;
    }
    
    .nyc-data-section {
        background: white;
        border-radius: 8px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        margin-top: 2rem;
        overflow: hidden;
    }
    
    .nyc-data-section h2 {
        text-align: left;
        color: #1a1a1a;
        margin-bottom: 0.5rem;
        font-size: 1.5rem;
        font-weight: 600;
    }
    
    .filter-row {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1rem;
        flex-wrap: wrap;
        align-items: flex-end;
    }
    
    .filter-item {
        display: flex;
        flex-direction: column;
        min-width: 120px;
    }
    
    .filter-item label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #555;
        margin-bottom: 0.25rem;
        text-transform: uppercase;
    }
    
    .filter-item input,
    .filter-item select {
        padding: 0.5rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 0.85rem;
        background: white;
    }
    
    .btn-apply {
        background: #4a90e2;
        color: white;
        border: none;
        padding: 0.5rem 1.5rem;
        border-radius: 4px;
        cursor: pointer;
        font-weight: 500;
        height: 35px;
        margin-top: auto;
    }
    
    .btn-apply:hover {
        background: #357abd;
    }
    
    .btn-reset {
        background: #f5f5f5;
        color: #333;
        border: 1px solid #ddd;
        padding: 0.5rem 1.5rem;
        border-radius: 4px;
        cursor: pointer;
        font-weight: 500;
        height: 35px;
        margin-top: auto;
    }
    
    .table-container {
        overflow-x: auto;
        margin-top: 1rem;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
    }
    
    .data-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.85rem;
        min-width: 1200px;
    }
    
    .data-table thead {
        background: #f8f9fa;
        border-bottom: 2px solid #e0e0e0;
    }
    
    .data-table th {
        padding: 0.75rem;
        text-align: left;
        font-weight: 600;
        color: #333;
        white-space: nowrap;
        border-right: 1px solid #e0e0e0;
        font-size: 0.8rem;
    }
    
    .data-table td {
        padding: 0.6rem 0.75rem;
        border-bottom: 1px solid #f0f0f0;
        border-right: 1px solid #f0f0f0;
        white-space: nowrap;
    }
    
    .data-table tbody tr:hover {
        background: #f8f9ff;
    }
    
    .pagination {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 1rem;
    }
    
    .pagination-buttons button {
        padding: 0.4rem 0.8rem;
        border: 1px solid #ddd;
        background: white;
        border-radius: 4px;
        cursor: pointer;
        margin: 0 0.25rem;
    }
    
    .pagination-buttons button.active {
        background: #4a90e2;
        color: white;
    }
    
    .empty-state {
        text-align: center;
        padding: 3rem;
        color: #999;
    }
    
    .empty-state .icon {
        font-size: 4rem;
        margin-bottom: 1rem;
    }
</style>

<div class="container">
    <!-- User Welcome Section -->
    <div class="dashboard-card">
        <h2>Welcome, <?php echo htmlspecialchars($user['username']); ?>! 👋</h2>
        
        <div class="user-info">
            <div class="info-item">
                <span class="info-label">👤 Username:</span>
                <span><?php echo htmlspecialchars($user['username']); ?></span>
            </div>
            
            <div class="info-item">
                <span class="info-label">📧 Email:</span>
                <span><?php echo htmlspecialchars($user['email']); ?></span>
            </div>
            
            <div class="info-item">
                <span class="info-label">📅 Member Since:</span>
                <span><?php echo date('F j, Y', strtotime($user['created_at'])); ?></span>
            </div>
        </div>
        
        <div class="profile-actions">
            <button class="btn btn-primary" onclick="alert('Edit profile coming soon!')">Edit Profile</button>
            <button class="btn btn-secondary" onclick="window.location.href='logout.php'">Logout</button>
        </div>
    </div>
    
    <!-- API Input Section -->
    <div class="api-input-section">
        <h3>🔌 Pull Data from NYC Open Data API</h3>
        <p>Enter an API endpoint URL to fetch and store collision data in your database</p>
        
        <div class="api-input-row">
            <input 
                type="text" 
                id="apiUrl" 
                class="api-url-input" 
                placeholder="https://data.cityofnewyork.us/resource/h9gi-nx95.json?$limit=100"
                value="https://data.cityofnewyork.us/resource/h9gi-nx95.json?$limit=500"
            >
            <button class="btn-fetch" id="fetchBtn" onclick="fetchAPIData()">
                📥 Pull Data
            </button>
        </div>
        
        <?php if ($recent_apis->num_rows > 0): ?>
        <div class="recent-apis">
            <span style="font-size: 0.8rem; opacity: 0.8;">Recent:</span>
            <?php while ($api = $recent_apis->fetch_assoc()): ?>
                <span class="api-tag" onclick="document.getElementById('apiUrl').value='<?php echo htmlspecialchars($api['api_url']); ?>'" title="<?php echo htmlspecialchars($api['api_url']); ?>">
                    <?php echo htmlspecialchars($api['endpoint_name']); ?>
                </span>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>
        
        <div id="statusMessage" class="status-message"></div>
    </div>
    
    <!-- Data Statistics -->
    <div class="data-stats">
        <div class="stat-badge">
            <div class="label">Total Records</div>
            <div class="value" id="totalRecords"><?php echo $total_records; ?></div>
        </div>
        <div class="stat-badge">
            <div class="label">Last Updated</div>
            <div class="value" style="font-size: 1rem;" id="lastUpdated">
                <?php 
                $conn = getDBConnection();
                $last = $conn->query("SELECT MAX(created_at) as last FROM nyc_collisions")->fetch_assoc();
                echo $last['last'] ? date('M d, H:i', strtotime($last['last'])) : 'Never';
                $conn->close();
                ?>
            </div>
        </div>
    </div>
    
    <!-- NYC Motor Vehicle Collisions Data Section -->
    <div class="nyc-data-section">
        <h2># Motor Vehicle Collisions - Crashes</h2>
        <p style="color: #666; margin-bottom: 1rem;">Data from your database</p>
        
        <!-- Filter Row -->
        <div class="filter-row">
            <div class="filter-item">
                <label>CRASH DATE</label>
                <input type="date" id="crashDate">
            </div>
            
            <div class="filter-item">
                <label>BOROUGH</label>
                <select id="borough">
                    <option value="">All</option>
                    <option value="BRONX">BRONX</option>
                    <option value="BROOKLYN">BROOKLYN</option>
                    <option value="MANHATTAN">MANHATTAN</option>
                    <option value="QUEENS">QUEENS</option>
                    <option value="STATEN ISLAND">STATEN ISLAND</option>
                </select>
            </div>
            
            <div class="filter-item">
                <label>ZIP CODE</label>
                <input type="text" id="zipCode" placeholder="Search...">
            </div>
            
            <div class="filter-item">
                <label>ON STREET NAME</label>
                <input type="text" id="onStreetName" placeholder="Search...">
            </div>
            
            <div class="filter-item">
                <label>CROSS STREET</label>
                <input type="text" id="crossStreet" placeholder="Search...">
            </div>
            
            <div class="filter-item">
                <label>PERSONS INJURED</label>
                <input type="number" id="personsInjured" placeholder="Min" min="0">
            </div>
            
            <button class="btn-apply" onclick="loadStoredData()">Apply Filters</button>
            <button class="btn-reset" onclick="resetFilters()">Reset</button>
        </div>
        
        <!-- Table -->
        <div class="table-container">
            <table class="data-table" id="collisionTable">
                <thead>
                    <tr>
                        <th>CRASH DATE</th>
                        <th>CRASH TIME</th>
                        <th>BOROUGH</th>
                        <th>ZIP CODE</th>
                        <th>LATITUDE</th>
                        <th>LONGITUDE</th>
                        <th>LOCATION</th>
                        <th>ON STREET NAME</th>
                        <th>CROSS STREET</th>
                        <th>PERSONS INJURED</th>
                        <th>PERSONS KILLED</th>
                        <th>CONTRIBUTING FACTOR</th>
                        <th>VEHICLE TYPE</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <tr>
                        <td colspan="13">
                            <div class="empty-state">
                                <div class="icon">📥</div>
                                <p>No data loaded yet. Use the API input above to pull data from NYC Open Data.</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="pagination" id="pagination" style="display: none;">
            <div id="paginationInfo"></div>
            <div class="pagination-buttons" id="paginationButtons"></div>
        </div>
    </div>
</div>

<script>
let currentPage = 0;

// Fetch data from API and store in database
async function fetchAPIData() {
    const apiUrl = document.getElementById('apiUrl').value;
    const fetchBtn = document.getElementById('fetchBtn');
    const statusDiv = document.getElementById('statusMessage');
    
    if (!apiUrl) {
        showStatus('Please enter an API URL', 'error');
        return;
    }
    
    // Disable button and show loading
    fetchBtn.disabled = true;
    fetchBtn.textContent = '⏳ Fetching...';
    showStatus('Fetching data from API...', 'loading');
    
    try {
        const formData = new FormData();
        formData.append('api_url', apiUrl);
        
        const response = await fetch('fetch_api.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.error) {
            showStatus('Error: ' + result.error, 'error');
        } else {
            showStatus('✅ ' + result.message, 'success');
            // Reload the stored data
            setTimeout(() => {
                loadStoredData();
                location.reload();
            }, 1500);
        }
    } catch (error) {
        showStatus('Error: ' + error.message, 'error');
    } finally {
        fetchBtn.disabled = false;
        fetchBtn.textContent = '📥 Pull Data';
    }
}

// Load data from database
async function loadStoredData(page = 0) {
    currentPage = page;
    
    document.getElementById('tableBody').innerHTML = '<tr><td colspan="13" style="text-align:center; padding:2rem;">Loading data...</td></tr>';
    
    // Build query parameters
    const params = new URLSearchParams();
    params.append('page', page);
    
    const crashDate = document.getElementById('crashDate').value;
    const borough = document.getElementById('borough').value;
    const zipCode = document.getElementById('zipCode').value;
    const onStreetName = document.getElementById('onStreetName').value;
    const crossStreet = document.getElementById('crossStreet').value;
    const personsInjured = document.getElementById('personsInjured').value;
    
    if (crashDate) params.append('crash_date', crashDate);
    if (borough) params.append('borough', borough);
    if (zipCode) params.append('zip_code', zipCode);
    if (onStreetName) params.append('on_street_name', onStreetName);
    if (crossStreet) params.append('cross_street', crossStreet);
    if (personsInjured) params.append('persons_injured', personsInjured);
    
    try {
        const response = await fetch(`load_data.php?${params.toString()}`);
        const result = await response.json();
        
        updateTable(result.data);
        updatePagination(result.total, result.page, result.limit);
    } catch (error) {
        document.getElementById('tableBody').innerHTML = '<tr><td colspan="13" style="text-align:center; padding:2rem; color:red;">Error loading data</td></tr>';
    }
}

// Update table with data
function updateTable(data) {
    const tableBody = document.getElementById('tableBody');
    
    if (data.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="13"><div class="empty-state"><div class="icon">📭</div><p>No data found. Try different filters or pull data first.</p></div></td></tr>';
        document.getElementById('pagination').style.display = 'none';
        return;
    }
    
    tableBody.innerHTML = '';
    
    data.forEach(item => {
        const row = document.createElement('tr');
        
        // Format date
        let crashDate = '';
        if (item.crash_date) {
            const date = new Date(item.crash_date);
            crashDate = date.toLocaleDateString('en-US', {
                month: '2-digit',
                day: '2-digit',
                year: 'numeric'
            });
        }
        
        // Format time
        let crashTime = item.crash_time || '';
        if (crashTime && crashTime.length > 5) {
            crashTime = crashTime.substring(0, 5);
        }
        
        row.innerHTML = `
            <td>${crashDate}</td>
            <td>${crashTime}</td>
            <td>${item.borough || ''}</td>
            <td>${item.zip_code || ''}</td>
            <td>${item.latitude || ''}</td>
            <td>${item.longitude || ''}</td>
            <td>${item.location || ''}</td>
            <td>${item.on_street_name || ''}</td>
            <td>${item.cross_street_name || ''}</td>
            <td>${item.number_of_persons_injured || 0}</td>
            <td>${item.number_of_persons_killed || 0}</td>
            <td>${item.contributing_factor_vehicle_1 || ''}</td>
            <td>${item.vehicle_type_code_1 || ''}</td>
        `;
        
        tableBody.appendChild(row);
    });
}

// Update pagination controls
function updatePagination(total, page, limit) {
    const paginationDiv = document.getElementById('pagination');
    const totalPages = Math.ceil(total / limit);
    
    if (totalPages <= 1) {
        paginationDiv.style.display = 'none';
        return;
    }
    
    paginationDiv.style.display = 'flex';
    document.getElementById('paginationInfo').textContent = `Page ${page + 1} of ${totalPages} (${total} total records)`;
    
    const buttonsDiv = document.getElementById('paginationButtons');
    buttonsDiv.innerHTML = '';
    
    // Previous button
    const prevBtn = document.createElement('button');
    prevBtn.textContent = '← Previous';
    prevBtn.disabled = page === 0;
    prevBtn.onclick = () => loadStoredData(page - 1);
    buttonsDiv.appendChild(prevBtn);
    
    // Page number buttons
    const startPage = Math.max(0, page - 2);
    const endPage = Math.min(totalPages, page + 3);
    
    for (let i = startPage; i < endPage; i++) {
        const pageBtn = document.createElement('button');
        pageBtn.textContent = i + 1;
        if (i === page) {
            pageBtn.classList.add('active');
        }
        pageBtn.onclick = () => loadStoredData(i);
        buttonsDiv.appendChild(pageBtn);
    }
    
    // Next button
    const nextBtn = document.createElement('button');
    nextBtn.textContent = 'Next →';
    nextBtn.disabled = page >= totalPages - 1;
    nextBtn.onclick = () => loadStoredData(page + 1);
    buttonsDiv.appendChild(nextBtn);
}

// Show status message
function showStatus(message, type) {
    const statusDiv = document.getElementById('statusMessage');
    statusDiv.textContent = message;
    statusDiv.className = 'status-message status-' + type;
    statusDiv.style.display = 'block';
    
    if (type === 'success' || type === 'error') {
        setTimeout(() => {
            statusDiv.style.display = 'none';
        }, 5000);
    }
}

// Reset all filters
function resetFilters() {
    document.getElementById('crashDate').value = '';
    document.getElementById('borough').value = '';
    document.getElementById('zipCode').value = '';
    document.getElementById('onStreetName').value = '';
    document.getElementById('crossStreet').value = '';
    document.getElementById('personsInjured').value = '';
    loadStoredData();
}

// Load data when page loads
window.onload = function() {
    loadStoredData();
};
</script>

<?php include 'includes/footer.php'; ?>