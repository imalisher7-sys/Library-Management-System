<?php
session_start();
require_once __DIR__ . '/../config.php';

$method = $_SERVER['REQUEST_METHOD'];

const BOOK_SELECT = '
    SELECT b.Book_ID, b.Title, b.ISBN, b.Publication_Year,
           b.Author_ID, b.Category_ID,
           a.Author_Name, c.Category_Name
    FROM book b
    INNER JOIN author a ON b.Author_ID = a.Author_ID
    INNER JOIN category c ON b.Category_ID = c.Category_ID
';

try {
    if ($method === 'GET' && isset($_GET['meta'])) {
        $authors = getDB()->query('SELECT Author_ID, Author_Name FROM author ORDER BY Author_Name')->fetchAll(PDO::FETCH_ASSOC);
        $categories = getDB()->query('SELECT Category_ID, Category_Name FROM category ORDER BY Category_Name')->fetchAll(PDO::FETCH_ASSOC);
        jsonResponse(['success' => true, 'authors' => $authors, 'categories' => $categories]);
    }

    if ($method === 'GET') {
        $search = trim($_GET['search'] ?? '');
        if ($search !== '') {
            $stmt = getDB()->prepare(
                BOOK_SELECT . '
                WHERE b.Title LIKE ? OR a.Author_Name LIKE ? OR c.Category_Name LIKE ? OR b.ISBN LIKE ?
                ORDER BY b.Title ASC'
            );
            $like = '%' . $search . '%';
            $stmt->execute([$like, $like, $like, $like]);
        } else {
            $stmt = getDB()->query(BOOK_SELECT . ' ORDER BY b.Title ASC');
        }

        $books = array_map('normalizeBook', $stmt->fetchAll(PDO::FETCH_ASSOC));
        jsonResponse(['success' => true, 'books' => $books]);
    }

    requireLogin();

    if ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $fields = validateBookInput($data);

        $stmt = getDB()->prepare(
            'INSERT INTO book (Title, ISBN, Publication_Year, Author_ID, Category_ID)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $fields['title'],
            $fields['isbn'],
            $fields['year'],
            $fields['author_id'],
            $fields['category_id'],
        ]);

        jsonResponse(['success' => true, 'message' => 'Book added.', 'id' => (int) getDB()->lastInsertId()], 201);
    }

    if ($method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = (int) ($data['id'] ?? 0);
        if ($id <= 0) {
            jsonResponse(['success' => false, 'message' => 'Invalid book ID.'], 400);
        }

        $fields = validateBookInput($data);
        $stmt = getDB()->prepare(
            'UPDATE book SET Title = ?, ISBN = ?, Publication_Year = ?, Author_ID = ?, Category_ID = ?
             WHERE Book_ID = ?'
        );
        $stmt->execute([
            $fields['title'],
            $fields['isbn'],
            $fields['year'],
            $fields['author_id'],
            $fields['category_id'],
            $id,
        ]);

        if ($stmt->rowCount() === 0) {
            jsonResponse(['success' => false, 'message' => 'Book not found.'], 404);
        }

        jsonResponse(['success' => true, 'message' => 'Book updated.']);
    }

    if ($method === 'DELETE') {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            jsonResponse(['success' => false, 'message' => 'Invalid book ID.'], 400);
        }

        $stmt = getDB()->prepare('DELETE FROM book WHERE Book_ID = ?');
        $stmt->execute([$id]);

        if ($stmt->rowCount() === 0) {
            jsonResponse(['success' => false, 'message' => 'Book not found.'], 404);
        }

        jsonResponse(['success' => true, 'message' => 'Book deleted.']);
    }

    jsonResponse(['success' => false, 'message' => 'Invalid request method.'], 405);
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        jsonResponse(['success' => false, 'message' => 'ISBN already exists.'], 409);
    }
    jsonResponse(['success' => false, 'message' => 'Database error. Import blockshelf_db.sql first.'], 500);
}

function validateBookInput(array $data): array
{
    $title = trim($data['title'] ?? '');
    $isbn = trim($data['isbn'] ?? '');
    $year = trim((string) ($data['publication_year'] ?? ''));
    $authorId = (int) ($data['author_id'] ?? 0);
    $categoryId = (int) ($data['category_id'] ?? 0);

    if ($title === '' || $authorId <= 0 || $categoryId <= 0) {
        jsonResponse(['success' => false, 'message' => 'Title, author, and category are required.'], 400);
    }

    return [
        'title' => $title,
        'isbn' => $isbn !== '' ? $isbn : null,
        'year' => $year !== '' ? $year : null,
        'author_id' => $authorId,
        'category_id' => $categoryId,
    ];
}

function normalizeBook(array $row): array
{
    return [
        'id' => (int) $row['Book_ID'],
        'title' => $row['Title'],
        'isbn' => $row['ISBN'],
        'publication_year' => $row['Publication_Year'],
        'author_id' => (int) $row['Author_ID'],
        'category_id' => (int) $row['Category_ID'],
        'author' => $row['Author_Name'],
        'category' => $row['Category_Name'],
    ];
}
