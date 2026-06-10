<?php
include 'db.php';

if (!isset($_SESSION['member_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: login.php");
    exit();
}

/* ADD BOOK */
if (isset($_POST['add_book'])) {

    $title = $_POST['title'];
    $isbn = $_POST['isbn'];
    $year = $_POST['year'];
    $author = $_POST['author'];
    $category = $_POST['category'];

    $sql = "INSERT INTO Book (Title, ISBN, Publication_Year, Author_ID, Category_ID)
            VALUES ('$title', '$isbn', '$year', '$author', '$category')";

    mysqli_query($conn, $sql);
    header("Location: books.php");
    exit();
}

/* DELETE BOOK */
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM Book WHERE Book_ID=$id");
    header("Location: books.php");
    exit();
}

/* FETCH BOOKS */
$books = mysqli_query($conn, "
SELECT b.*, a.Author_Name, c.Category_Name
FROM Book b
JOIN Author a ON b.Author_ID = a.Author_ID
JOIN Category c ON b.Category_ID = c.Category_ID
");

/* DROPDOWNS */
$authors = mysqli_query($conn, "SELECT * FROM Author");
$categories = mysqli_query($conn, "SELECT * FROM Category");
?>

<!DOCTYPE html>
<html>
<head>
<title>Books</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="card">

<h2>Add Book (Admin Only)</h2>

<form method="POST">

<input type="text" name="title" placeholder="Book Title" required>
<input type="text" name="isbn" placeholder="ISBN">
<input type="number" name="year" placeholder="Publication Year">

<select name="author" required>
<option value="">Select Author</option>
<?php while($a = mysqli_fetch_assoc($authors)) { ?>
<option value="<?php echo $a['Author_ID']; ?>">
<?php echo $a['Author_Name']; ?>
</option>
<?php } ?>
</select>

<select name="category" required>
<option value="">Select Category</option>
<?php while($c = mysqli_fetch_assoc($categories)) { ?>
<option value="<?php echo $c['Category_ID']; ?>">
<?php echo $c['Category_Name']; ?>
</option>
<?php } ?>
</select>

<button type="submit" name="add_book">Add Book</button>

</form>

</div>

<hr>

<h2>All Books</h2>

<table border="1" width="100%">
<tr>
<th>ID</th>
<th>Title</th>
<th>ISBN</th>
<th>Year</th>
<th>Author</th>
<th>Category</th>
<th>Action</th>
</tr>

<?php while($b = mysqli_fetch_assoc($books)) { ?>
<tr>
<td><?php echo $b['Book_ID']; ?></td>
<td><?php echo $b['Title']; ?></td>
<td><?php echo $b['ISBN']; ?></td>
<td><?php echo $b['Publication_Year']; ?></td>
<td><?php echo $b['Author_Name']; ?></td>
<td><?php echo $b['Category_Name']; ?></td>
<td>
<a href="books.php?delete=<?php echo $b['Book_ID']; ?>">Delete</a>
</td>
</tr>
<?php } ?>

</table>

<br>
<a href="logout.php">Logout</a>

</body>
</html>