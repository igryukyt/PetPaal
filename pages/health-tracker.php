<?php
/**
 * PetPal - Health Tracker Page
 * Pet health records management
 */

require_once '../config/config.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    setFlash('error', 'Please login to access the health tracker.');
    redirect(SITE_URL . '/pages/login.php');
}

$conn = getDBConnection();
$userId = $_SESSION['user_id'];

// Fetch user's health records
$stmt = $conn->prepare("
    SELECT * FROM health_records 
    WHERE user_id = ? 
    ORDER BY checkup_date DESC, created_at DESC
");
$stmt->execute([$userId]);
$records = $stmt->fetchAll();

// Get unique pet names for filter
$petNames = array_unique(array_column($records, 'pet_name'));

$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Track your pet's health records and medical checkups">
    <title>Health Tracker - <?php echo SITE_NAME; ?></title>
    <?php include '../includes/head.php'; ?>
    <style>
        .tracker-layout {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 30px;
            align-items: start;
        }
        
        @media (max-width: 992px) {
            .tracker-layout {
                grid-template-columns: 1fr;
            }
        }
        
        .form-card {
            position: sticky;
            top: 100px;
        }
        
        .record-timeline {
            position: relative;
            padding-left: 30px;
        }
        
        .record-timeline::before {
            content: '';
            position: absolute;
            left: 8px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: var(--gray-light);
        }
        
        .health-record {
            position: relative;
            margin-bottom: 25px;
        }
        
        .health-record::before {
            content: '';
            position: absolute;
            left: -26px;
            top: 25px;
            width: 14px;
            height: 14px;
            background: var(--primary);
            border-radius: 50%;
            border: 3px solid var(--white);
            box-shadow: var(--shadow);
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <!-- Page Header -->
    <header class="page-header">
        <div class="container">
            <div class="breadcrumb">
                <a href="<?php echo SITE_URL; ?>">Home</a>
                <i class="fas fa-chevron-right"></i>
                <span>Health Tracker</span>
            </div>
            <h1>Pet Health Tracker</h1>
            <p>Keep track of your pet's medical records and checkups</p>
        </div>
    </header>
    
    <main class="main-content">
        <div class="container">
            <?php $flash = getFlash(); if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type'] === 'success' ? 'success' : 'error'; ?>">
                    <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check' : 'exclamation'; ?>-circle"></i>
                    <?php echo h($flash['message']); ?>
                </div>
            <?php endif; ?>
            
            <div class="tracker-layout">
                <!-- Add Record Form -->
                <div>
                    <div class="card form-card" style="padding: 25px;">
                        <h3 style="margin-bottom: 20px;">
                            <i class="fas fa-plus-circle" style="color: var(--primary);"></i>
                            Add Health Record
                        </h3>
                        
                        <form id="healthForm">
                            <input type="hidden" name="csrf_token" value="<?php echo h($csrfToken); ?>">
                            
                            <div class="form-group">
                                <label class="form-label">Pet Name *</label>
                                <input type="text" 
                                       name="pet_name" 
                                       class="form-control" 
                                       placeholder="Enter pet's name"
                                       required
                                       list="petNamesList">
                                <datalist id="petNamesList">
                                    <?php foreach ($petNames as $name): ?>
                                        <option value="<?php echo h($name); ?>">
                                    <?php endforeach; ?>
                                </datalist>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Checkup Date *</label>
                                <input type="date" 
                                       name="checkup_date" 
                                       class="form-control"
                                       required
                                       max="<?php echo date('Y-m-d'); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Veterinarian Name</label>
                                <input type="text" 
                                       name="vet_name" 
                                       class="form-control" 
                                       placeholder="Dr. Smith">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Diagnosis / Reason</label>
                                <input type="text" 
                                       name="diagnosis" 
                                       class="form-control" 
                                       placeholder="Annual checkup, vaccination, etc.">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Treatment / Prescription</label>
                                <textarea name="treatment" 
                                          class="form-control" 
                                          rows="3" 
                                          placeholder="Medications, procedures, etc."></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Next Appointment</label>
                                <input type="date" 
                                       name="next_appointment" 
                                       class="form-control"
                                       min="<?php echo date('Y-m-d'); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Additional Notes</label>
                                <textarea name="notes" 
                                          class="form-control" 
                                          rows="2" 
                                          placeholder="Weight, vitals, observations..."></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-block" id="submitBtn">
                                <i class="fas fa-save"></i>
                                Save Record
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Records List -->
                <div>
                    <?php if (!empty($petNames)): ?>
                        <div class="shop-filters" style="margin-bottom: 30px;">
                            <div class="filter-group">
                                <label class="filter-label">Filter by Pet:</label>
                                <select class="filter-select" id="petFilter">
                                    <option value="">All Pets</option>
                                    <?php foreach ($petNames as $name): ?>
                                        <option value="<?php echo h($name); ?>"><?php echo h($name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (empty($records)): ?>
                        <div class="empty-state">
                            <div class="empty-icon">📋</div>
                            <h3>No Health Records Yet</h3>
                            <p>Start tracking your pet's health by adding a record.</p>
                        </div>
                    <?php else: ?>
                        <div class="record-timeline" id="recordsList">
                            <?php foreach ($records as $record): ?>
                                <div class="health-record" data-pet="<?php echo h($record['pet_name']); ?>">
                                    <div class="health-record-header">
                                        <h4 class="health-pet-name">
                                            <i class="fas fa-paw" style="margin-right: 5px;"></i>
                                            <?php echo h($record['pet_name']); ?>
                                        </h4>
                                        <span class="health-date">
                                            <i class="fas fa-calendar"></i>
                                            <?php echo date('M j, Y', strtotime($record['checkup_date'])); ?>
                                        </span>
                                    </div>
                                    
                                    <div class="health-details">
                                        <?php if ($record['vet_name']): ?>
                                            <div class="health-detail-item">
                                                <div class="health-detail-label">Veterinarian</div>
                                                <div class="health-detail-value"><?php echo h($record['vet_name']); ?></div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($record['diagnosis']): ?>
                                            <div class="health-detail-item">
                                                <div class="health-detail-label">Diagnosis</div>
                                                <div class="health-detail-value"><?php echo h($record['diagnosis']); ?></div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($record['treatment']): ?>
                                            <div class="health-detail-item">
                                                <div class="health-detail-label">Treatment</div>
                                                <div class="health-detail-value"><?php echo h($record['treatment']); ?></div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($record['next_appointment']): ?>
                                            <div class="health-detail-item">
                                                <div class="health-detail-label">Next Appointment</div>
                                                <div class="health-detail-value">
                                                    <?php 
                                                    $nextDate = strtotime($record['next_appointment']);
                                                    $isUpcoming = $nextDate >= strtotime('today');
                                                    ?>
                                                    <span style="color: <?php echo $isUpcoming ? 'var(--success)' : 'var(--gray)'; ?>;">
                                                        <?php echo date('M j, Y', $nextDate); ?>
                                                        <?php if ($isUpcoming): ?>
                                                            <i class="fas fa-bell"></i>
                                                        <?php endif; ?>
                                                    </span>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($record['notes']): ?>
                                            <div class="health-detail-item" style="grid-column: 1 / -1;">
                                                <div class="health-detail-label">Notes</div>
                                                <div class="health-detail-value"><?php echo nl2br(h($record['notes'])); ?></div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div style="margin-top: 15px; text-align: right;">
                                        <button class="btn btn-sm btn-danger" onclick="deleteRecord(<?php echo $record['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    
    <script>
        // Form submission
        document.getElementById('healthForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const btn = document.getElementById('submitBtn');
            
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            
            fetch('<?php echo SITE_URL; ?>/api/health-actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Health record saved successfully!');
                    location.reload();
                } else {
                    alert(data.message || 'Error saving record');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-save"></i> Save Record';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save"></i> Save Record';
            });
        });
        
        // Delete record
        function deleteRecord(recordId) {
            if (!confirm('Are you sure you want to delete this record?')) return;
            
            fetch('<?php echo SITE_URL; ?>/api/health-actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=delete&record_id=' + recordId + '&csrf_token=<?php echo h($csrfToken); ?>'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error deleting record');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        }
        
        // Filter by pet
        document.getElementById('petFilter')?.addEventListener('change', function() {
            const selectedPet = this.value.toLowerCase();
            
            document.querySelectorAll('.health-record').forEach(record => {
                const petName = record.dataset.pet.toLowerCase();
                if (!selectedPet || petName === selectedPet) {
                    record.style.display = 'block';
                } else {
                    record.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
