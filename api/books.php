<?php
include 'db.php';

if (!isset($_SESSION['member_id'])) {
    header('Location: login.php');
    exit();
}

$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'Admin';

if ($isAdmin && isset($_POST['add_book'])) {
    $title    = esc($conn, $_POST['title'] ?? '');
    $isbn     = esc($conn, $_POST['isbn'] ?? '');
    $year     = esc($conn, $_POST['year'] ?? '');
    $author   = (int) ($_POST['author'] ?? 0);
    $category = (int) ($_POST['category'] ?? 0);

    if ($title !== '' && $author > 0 && $category > 0) {
        $sql = "INSERT INTO book (Title, ISBN, Publication_Year, Author_ID, Category_ID)
                VALUES ('$title', " . ($isbn !== '' ? "'$isbn'" : 'NULL') . ", " .
                ($year !== '' ? "'$year'" : 'NULL') . ", $author, $category)";
        mysqli_query($conn, $sql);
    }

    header('Location: books.php');
    exit();
}

if ($isAdmin && isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    if ($id > 0) {
        mysqli_query($conn, "DELETE FROM book WHERE Book_ID = $id");
    }
    header('Location: books.php');
    exit();
}

$books = mysqli_query($conn, "
    SELECT b.*, a.Author_Name, c.Category_Name
    FROM book b
    JOIN author a ON b.Author_ID = a.Author_ID
    JOIN category c ON b.Category_ID = c.Category_ID
    ORDER BY b.Title ASC
");

$authors    = mysqli_query($conn, 'SELECT * FROM author ORDER BY Author_Name ASC');
$categories = mysqli_query($conn, 'SELECT * FROM category ORDER BY Category_Name ASC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Library Books</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="center-wrapper" style="display:block; padding:24px;">

<div class="card" style="max-width:900px; margin:0 auto 24px;">
<h2>Welcome, <?php echo htmlspecialchars($_SESSION['name'] ?? 'Reader'); ?></h2>
<p>You are logged in as <strong><?php echo htmlspecialchars($_SESSION['role'] ?? 'Member'); ?></strong></p>
<a class="link" href="../index.html">Home</a> |
<a class="link" href="logout.php">Logout</a>
</div>

<?php if ($isAdmin) { ?>
<div class="card" style="max-width:900px; margin:0 auto 24px;">
<h2>Add Book (Admin)</h2>
<form method="POST">
<input type="text" name="title" placeholder="Book Title" required>
<input type="text" name="isbn" placeholder="ISBN">
<input type="number" name="year" placeholder="Publication Year" min="1000" max="2100">
<select name="author" required>
<option value="">Select Author</option>
<?php while ($a = mysqli_fetch_assoc($authors)) { ?>
<option value="<?php echo (int) $a['Author_ID']; ?>"><?php echo htmlspecialchars($a['Author_Name']); ?></option>
<?php } ?>
</select>
<select name="category" required>
<option value="">Select Category</option>
<?php while ($c = mysqli_fetch_assoc($categories)) { ?>
<option value="<?php echo (int) $c['Category_ID']; ?>"><?php echo htmlspecialchars($c['Category_Name']); ?></option>
<?php } ?>
</select>
<button type="submit" name="add_book">Add Book</button>
</form>
</div>
<?php } ?>

<div class="card" style="max-width:900px; margin:0 auto;">
<h2>All Books</h2>
<table border="1" width="100%" cellpadding="8" style="border-collapse:collapse;">
<tr>
<th>Title</th>
<th>ISBN</th>
<th>Year</th>
<th>Author</th>
<th>Category</th>
<?php if ($isAdmin) { ?><th>Action</th><?php } ?>
</tr>
<?php while ($b = mysqli_fetch_assoc($books)) { ?>
<tr>
<td><?php echo htmlspecialchars($b['Title']); ?></td>
<td><?php echo htmlspecialchars($b['ISBN'] ?? 'N/A'); ?></td>
<td><?php echo htmlspecialchars($b['Publication_Year'] ?? 'N/A'); ?></td>
<td><?php echo htmlspecialchars($b['Author_Name']); ?></td>
<td><?php echo htmlspecialchars($b['Category_Name']); ?></td>
<?php if ($isAdmin) { ?>
<td><a href="books.php?delete=<?php echo (int) $b['Book_ID']; ?>">Delete</a></td>
<?php } ?>
</tr>
<?php } ?>
</table>
</div>

</body>
</html>
