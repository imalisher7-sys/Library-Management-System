const API = "api";

function showToast(msg, isError = false) {
  const el = document.getElementById("toast");
  if (!el) return;
  el.textContent = msg;
  el.style.background = isError ? "#e74c3c" : "#2ecc71";
  el.style.display = "block";
  setTimeout(() => (el.style.display = "none"), 2500);
}

async function apiFetch(url, options = {}) {
  const res = await fetch(url, {
    headers: { "Content-Type": "application/json", ...(options.headers || {}) },
    credentials: "same-origin",
    ...options,
  });
  const data = await res.json().catch(() => ({}));
  if (!res.ok) {
    throw new Error(data.message || "Request failed.");
  }
  return data;
}

function toggleTheme() {
  document.body.classList.toggle("light-theme");
  const icon = document.getElementById("themeIcon");
  if (icon) icon.textContent = document.body.classList.contains("light-theme") ? "☀️" : "🌙";
}

async function login() {
  const email = document.getElementById("loginEmail").value.trim();
  const password = document.getElementById("loginPassword").value;

  try {
    const data = await apiFetch(`${API}/login.php`, {
      method: "POST",
      body: JSON.stringify({ email, password }),
    });
    showToast(data.message);
    setTimeout(() => (window.location.href = "books.html"), 800);
  } catch (err) {
    showToast(err.message, true);
  }
}

async function signup() {
  const full_name = document.getElementById("signupName").value.trim();
  const email = document.getElementById("signupEmail").value.trim();
  const password = document.getElementById("signupPassword").value;

  try {
    const data = await apiFetch(`${API}/signup.php`, {
      method: "POST",
      body: JSON.stringify({ full_name, email, password }),
    });
    showToast(data.message);
    setTimeout(() => (window.location.href = "books.html"), 800);
  } catch (err) {
    showToast(err.message, true);
  }
}

async function resetPassword() {
  const email = document.getElementById("resetEmail").value.trim();

  try {
    const data = await apiFetch(`${API}/reset.php`, {
      method: "POST",
      body: JSON.stringify({ email }),
    });
    showToast(data.message);
  } catch (err) {
    showToast(err.message, true);
  }
}

async function logout() {
  try {
    await fetch(`${API}/logout.php`, { credentials: "same-origin" });
    window.location.href = "login.html";
  } catch {
    showToast("Logout failed.", true);
  }
}

let books = [];
let authors = [];
let categories = [];
let editId = null;

async function initBooksPage() {
  try {
    const session = await apiFetch(`${API}/session.php`);
    if (!session.logged_in) {
      window.location.href = "login.html";
      return;
    }
    const greeting = document.getElementById("userGreeting");
    if (greeting) greeting.textContent = `Hi, ${session.user.name}`;
    await loadMeta();
    await loadBooks();
  } catch {
    window.location.href = "login.html";
  }
}

async function loadMeta() {
  const data = await apiFetch(`${API}/books.php?meta=1`);
  authors = data.authors || [];
  categories = data.categories || [];
  fillSelect("bookAuthor", authors, "Author_ID", "Author_Name");
  fillSelect("bookCategory", categories, "Category_ID", "Category_Name");
}

function fillSelect(id, items, valueKey, labelKey) {
  const select = document.getElementById(id);
  if (!select) return;
  const current = select.value;
  select.innerHTML = `<option value="">Select ${labelKey.replace("_", " ")}</option>`;
  items.forEach((item) => {
    const opt = document.createElement("option");
    opt.value = item[valueKey];
    opt.textContent = item[labelKey];
    select.appendChild(opt);
  });
  if (current) select.value = current;
}

async function loadBooks() {
  try {
    const data = await apiFetch(`${API}/books.php`);
    books = data.books || [];
    renderBooks(books);
  } catch (err) {
    showToast(err.message, true);
  }
}

function bookPayload() {
  return {
    title: document.getElementById("bookTitle").value.trim(),
    isbn: document.getElementById("bookIsbn").value.trim(),
    publication_year: document.getElementById("bookYear").value.trim(),
    author_id: parseInt(document.getElementById("bookAuthor").value, 10),
    category_id: parseInt(document.getElementById("bookCategory").value, 10),
  };
}

function clearBookForm() {
  document.getElementById("bookTitle").value = "";
  document.getElementById("bookIsbn").value = "";
  document.getElementById("bookYear").value = "";
  document.getElementById("bookAuthor").value = "";
  document.getElementById("bookCategory").value = "";
  editId = null;
}

async function addBook() {
  const payload = bookPayload();
  if (!payload.title || !payload.author_id || !payload.category_id) {
    showToast("Title, author, and category are required.", true);
    return;
  }

  try {
    if (editId !== null) {
      await apiFetch(`${API}/books.php`, {
        method: "PUT",
        body: JSON.stringify({ id: editId, ...payload }),
      });
      showToast("Book updated");
    } else {
      await apiFetch(`${API}/books.php`, {
        method: "POST",
        body: JSON.stringify(payload),
      });
      showToast("Book added");
    }

    clearBookForm();
    await loadBooks();
  } catch (err) {
    showToast(err.message, true);
  }
}

function renderBooks(list) {
  const grid = document.getElementById("bookGrid");
  if (!grid) return;

  grid.innerHTML = "";

  if (list.length === 0) {
    grid.innerHTML = '<p style="opacity:0.8;">No books found.</p>';
    return;
  }

  list.forEach((book) => {
    const div = document.createElement("div");
    div.className = "book-card";
    div.innerHTML = `
      <h3>${escapeHtml(book.title)}</h3>
      <p><strong>Author:</strong> ${escapeHtml(book.author)}</p>
      <p><strong>Category:</strong> ${escapeHtml(book.category)}</p>
      <p><strong>ISBN:</strong> ${escapeHtml(book.isbn || "N/A")}</p>
      <p><strong>Year:</strong> ${escapeHtml(book.publication_year || "N/A")}</p>
      <button type="button" onclick="editBook(${book.id})">Edit</button>
      <button type="button" onclick="deleteBook(${book.id})">Delete</button>
    `;
    grid.appendChild(div);
  });
}

function escapeHtml(text) {
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}

function editBook(id) {
  const book = books.find((b) => b.id === id);
  if (!book) return;
  document.getElementById("bookTitle").value = book.title;
  document.getElementById("bookIsbn").value = book.isbn || "";
  document.getElementById("bookYear").value = book.publication_year || "";
  document.getElementById("bookAuthor").value = book.author_id;
  document.getElementById("bookCategory").value = book.category_id;
  editId = id;
  showToast("Editing book...");
}

async function deleteBook(id) {
  try {
    await apiFetch(`${API}/books.php?id=${id}`, { method: "DELETE" });
    showToast("Book deleted");
    if (editId === id) editId = null;
    await loadBooks();
  } catch (err) {
    showToast(err.message, true);
  }
}

let searchTimer = null;
function searchBooks() {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(async () => {
    const q = document.getElementById("searchInput").value.trim();
    try {
      const url = q ? `${API}/books.php?search=${encodeURIComponent(q)}` : `${API}/books.php`;
      const data = await apiFetch(url);
      books = data.books || [];
      renderBooks(books);
    } catch (err) {
      showToast(err.message, true);
    }
  }, 300);
}
