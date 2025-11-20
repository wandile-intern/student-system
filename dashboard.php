<?php
require 'config.php';

// fetch all
$stmt = $pdo->query("SELECT * FROM students ORDER BY full_name");
$students = $stmt->fetchAll(); // PHP array
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"><title>Dashboard</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
</head>
<body class="container">
  <h4>Student Dashboard</h4>

  <div class="input-field col s6">
    <input id="search" type="text">
    <label for="search">Search (name, student id, email, course)</label>
  </div>

  <table id="students-table" class="striped responsive-table">
    <thead>
      <tr>
        <th onclick="sortTable(0)">Name</th>
        <th onclick="sortTable(1)">Student ID</th>
        <th>Email</th>
        <th>Course</th>
        <th>Enrollment</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($students as $s): ?>
      <tr>
        <td><?=htmlspecialchars($s['full_name'])?></td>
        <td><?=htmlspecialchars($s['student_id'])?></td>
        <td><?=htmlspecialchars($s['email'])?></td>
        <td><?=htmlspecialchars($s['course'])?></td>
        <td><?=htmlspecialchars($s['enrollment_date'])?></td>

        <td>
            <a class="btn-small" href="profile.php?id=<?=htmlspecialchars($s['id'])?>">View</a>
            <a class="btn-small orange" href="update.php?id=<?=htmlspecialchars($s['id'])?>">Edit</a>
            <form method="post" action="delete.php" style="display:inline" onsubmit="return confirm('Delete student <?= htmlspecialchars(addslashes($s['full_name'])) ?>? This cannot be undone.');">
            <input type="hidden" name="id" value="<?=htmlspecialchars($s['id'])?>">
            <button type="submit" class="btn-small red">Delete</button>
            </form>


        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

<script>
document.getElementById('search').addEventListener('input', function(e){
  const q = e.target.value.toLowerCase();
  document.querySelectorAll('#students-table tbody tr').forEach(row => {
    const text = row.innerText.toLowerCase();
    row.style.display = text.includes(q) ? '' : 'none';
  });
});

function confirmDelete(id, name) {
  if (!confirm('Delete student "' + name + '"? This action is irreversible.')) return;
  const form = document.getElementById('delete-form-' + id);
  if (!form) { alert('Delete form missing for id ' + id); return; }
  form.submit();
}

// simple column sorting (by visible text)
function sortTable(colIndex) {
  const tbody = document.querySelector('#students-table tbody');
  const rows = Array.from(tbody.querySelectorAll('tr')).filter(r => r.style.display !== 'none');
  const asc = !tbody.dataset.asc || tbody.dataset.asc === 'false';
  rows.sort((a,b)=>{
    const A = a.children[colIndex].innerText.trim().toLowerCase();
    const B = b.children[colIndex].innerText.trim().toLowerCase();
    return (A>B ? 1 : (A<B ? -1 : 0)) * (asc ? 1 : -1);
  });
  rows.forEach(r=>tbody.appendChild(r));
  tbody.dataset.asc = asc;
}
</script>
</body>
</html>
