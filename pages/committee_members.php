<?php
/**
 * Committee Members Page
 * Display members of organizing sub-committees with chair indication
 * Requirements: 1.1, 1.2, 1.3, 1.4
 */

require_once '../config/database.php';
include '../includes/header.php';

// Get database connection
$pdo = getDBConnection();

// Get selected committee ID from GET parameter
$selectedCommitteeId = isset($_GET['committee_id']) ? (int)$_GET['committee_id'] : null;

// Fetch all committees for dropdown
$committeesStmt = $pdo->query("SELECT CommitteeID, CommitteeName FROM SubCommittee ORDER BY CommitteeName");
$committees = $committeesStmt->fetchAll();

// Fetch members if a committee is selected
$members = [];
$selectedCommitteeName = '';
if ($selectedCommitteeId) {
    // Get committee name
    $committeeNameStmt = $pdo->prepare("SELECT CommitteeName FROM SubCommittee WHERE CommitteeID = :committee_id");
    $committeeNameStmt->execute(['committee_id' => $selectedCommitteeId]);
    $committeeData = $committeeNameStmt->fetch();
    $selectedCommitteeName = $committeeData ? $committeeData['CommitteeName'] : '';
    
    // Get members of selected committee with chair indication
    $membersStmt = $pdo->prepare("
        SELECT cm.FirstName, cm.LastName, 
               (cm.MemberID = sc.ChairMemberID) AS IsChair
        FROM CommitteeMember cm
        JOIN MemberOfCommittee moc ON cm.MemberID = moc.MemberID
        JOIN SubCommittee sc ON moc.CommitteeID = sc.CommitteeID
        WHERE sc.CommitteeID = :committee_id
        ORDER BY IsChair DESC, cm.LastName, cm.FirstName
    ");
    $membersStmt->execute(['committee_id' => $selectedCommitteeId]);
    $members = $membersStmt->fetchAll();
}
?>

<div class="container">
    <h2>Committee Members</h2>
    <p>Select a sub-committee to view its members and chair.</p>
    
    <form method="GET" action="committee_members.php" class="form-inline">
        <div class="form-group">
            <label for="committee_id">Select Committee:</label>
            <select name="committee_id" id="committee_id" class="form-control" onchange="this.form.submit()">
                <option value="">-- Choose a Committee --</option>
                <?php foreach ($committees as $committee): ?>
                    <option value="<?php echo htmlspecialchars($committee['CommitteeID']); ?>"
                            <?php echo ($selectedCommitteeId == $committee['CommitteeID']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($committee['CommitteeName']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>
    
    <?php if ($selectedCommitteeId): ?>
        <div class="results-section">
            <h3><?php echo htmlspecialchars($selectedCommitteeName); ?></h3>
            
            <?php if (count($members) > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($members as $member): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($member['FirstName']); ?></td>
                                <td><?php echo htmlspecialchars($member['LastName']); ?></td>
                                <td>
                                    <?php if ($member['IsChair']): ?>
                                        <span class="badge badge-chair">Chair</span>
                                    <?php else: ?>
                                        Member
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p class="info-text">Total members: <?php echo count($members); ?></p>
            <?php else: ?>
                <p class="empty-state">No members found for this committee.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
