const API = "api";
const DB_KEY = "blockshelf_db";
const SESSION_KEY = "blockshelf_session";

let useLocal = true;
let backendReady = null;
let books = [];
let authors = [];
let categories = [];
let editId = null;

function showToast(msg, isError = false) {
  const el = document.getElementById("toast");
  if (!el) return;
  el.textContent = msg;
  el.style.background = isError ? "#e74c3c" : "#2ecc71";
  el.style.display = "block";
  setTimeout(() => (el.style.display = "none"), 2500);
}

function toggleTheme() {
  document.body.classList.toggle("light-theme");
  const icon = document.getElementById("themeIcon");
  if (icon) icon.textContent = document.body.classList.contains("light-theme") ? "☀️" : "🌙";
}

function escapeHtml(text) {
  const div = document.createElement("div");
  div.textContent = text ?? "";
  return div.innerHTML;
}

/* ── Local storage (works without XAMPP — just open the HTML files) ── */

function defaultDb() {
  return {
    members: [],
    authors: [
      { Author_ID: 1, Author_Name: "F. Scott Fitzgerald" },
      { Author_ID: 2, Author_Name: "George Orwell" },
      { Author_ID: 3, Author_Name: "Harper Lee" },
    ],
    categories: [
      { Category_ID: 1, Category_Name: "Fiction" },
      { Category_ID: 2, Category_Name: "Classic" },
      { Category_ID: 3, Category_Name: "Drama" },
    ],
    books: [
      { Book_ID: 1, Title: "The Great Gatsby", ISBN: "978-0743273565", Publication_Year: "1925", Author_ID: 1, Category_ID: 2 },
      { Book_ID: 2, Title: "1984", ISBN: "978-0451524935", Publication_Year: "1949", Author_ID: 2, Category_ID: 1 },
      { Book_ID: 3, Title: "To Kill a Mockingbird", ISBN: "978-0061120084", Publication_Year: "1960", Author_ID: 3, Category_ID: 3 },
    ],
    nextMemberId: 1,
    nextBookId: 4,
  };
}

function getDb() {
  const raw = localStorage.getItem(DB_KEY);
  if (!raw) {
    const db = defaultDb();
    localStorage.setItem(DB_KEY, JSON.stringify(db));
    return db;
  }
  return JSON.parse(raw);
}

function saveDb(db) {
  localStorage.setItem(DB_KEY, JSON.stringify(db));
}

function getSession() {
  const raw = sessionStorage.getItem(SESSION_KEY);
  return raw ? JSON.parse(raw) : null;
}

function setSession(user) {
  sessionStorage.setItem(SESSION_KEY, JSON.stringify(user));
}

function clearSession() {
  sessionStorage.removeItem(SESSION_KEY);
}

function bookView(db, book) {
  const author = db.authors.find((a) => a.Author_ID === book.Author_ID);
  const category = db.categories.find((c) => c.Category_ID === book.Category_ID);
  return {
    id: book.Book_ID,
    title: book.Title,
    isbn: book.ISBN,
    publication_year: book.Publication_Year,
    author_id: book.Author_ID,
    category_id: book.Category_ID,
    author: author ? author.Author_Name : "Unknown",
    category: category ? category.Category_Name : "Unknown",
  };
}

function localSignup(body) {
  const name = (body.full_name || "").trim();
  const email = (body.email || "").trim();
  const password = body.password || "";

  if (!name || !email || !password) throw new Error("Name, email, and password are required.");
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) throw new Error("Invalid email address.");
  if (password.length < 6) throw new Error("Password must be at least 6 characters.");

  const db = getDb();
  if (db.members.some((m) => m.Email.toLowerCase() === email.toLowerCase())) {
    throw new Error("Email already registered.");
  }

  const member = {
    Member_ID: db.nextMemberId++,
    Name: name,
    Email: email,
    Password: password,
    Membership_Status: "Active",
  };
  db.members.push(member);
  saveDb(db);
  setSession({ user_id: member.Member_ID, user_name: member.Name, user_email: member.Email });
  return { success: true, message: "Account created! Data saved in your browser." };
}

function localLogin(body) {
  const email = (body.email || "").trim();
  const password = body.password || "";
  if (!email || !password) throw new Error("Email and password are required.");

  const db = getDb();
  const member = db.members.find((m) => m.Email.toLowerCase() === email.toLowerCase());
  if (!member || member.Password !== password) throw new Error("Invalid email or password.");
  if (member.Membership_Status !== "Active") throw new Error("Your membership is inactive.");

  setSession({ user_id: member.Member_ID, user_name: member.Name, user_email: member.Email });
  return { success: true, message: "Login successful." };
}

function localReset(body) {
  const email = (body.email || "").trim();
  if (!email) throw new Error("Valid email is required.");
  const db = getDb();
  if (!db.members.some((m) => m.Email.toLowerCase() === email.toLowerCase())) {
    throw new Error("No account found with that email.");
  }
  return { success: true, message: "Reset link sent (demo mode)." };
}

function localSession() {
  const s = getSession();
  if (!s) return { logged_in: false };
  return { logged_in: true, user: { name: s.user_name, email: s.user_email } };
}

function localLogout() {
  clearSession();
  return { success: true };
}

function localMeta() {
  const db = getDb();
  return { success: true, authors: db.authors, categories: db.categories };
}

function localBooks(search = "") {
  const db = getDb();
  let list = db.books.map((b) => bookView(db, b));
  if (search) {
    const q = search.toLowerCase();
    list = list.filter(
      (b) =>
        b.title.toLowerCase().includes(q) ||
        b.author.toLowerCase().includes(q) ||
        b.category.toLowerCase().includes(q) ||
        (b.isbn || "").toLowerCase().includes(q)
    );
  }
  return { success: true, books: list };
}

function localSaveBook(body, isEdit) {
  const session = getSession();
  if (!session) throw new Error("Please log in first.");

  const title = (body.title || "").trim();
  const authorId = parseInt(body.author_id, 10);
  const categoryId = parseInt(body.category_id, 10);
  if (!title || !authorId || !categoryId) throw new Error("Title, author, and category are required.");

  const db = getDb();
  const record = {
    Title: title,
    ISBN: (body.isbn || "").trim() || null,
    Publication_Year: (body.publication_year || "").trim() || null,
    Author_ID: authorId,
    Category_ID: categoryId,
  };

  if (isEdit) {
    const idx = db.books.findIndex((b) => b.Book_ID === body.id);
    if (idx === -1) throw new Error("Book not found.");
    db.books[idx] = { ...db.books[idx], ...record };
    saveDb(db);
    return { success: true, message: "Book updated." };
  }

  db.books.push({ Book_ID: db.nextBookId++, ...record });
  saveDb(db);
  return { success: true, message: "Book added." };
}

function localDeleteBook(id) {
  const session = getSession();
  if (!session) throw new Error("Please log in first.");
  const db = getDb();
  const before = db.books.length;
  db.books = db.books.filter((b) => b.Book_ID !== id);
  if (db.books.length === before) throw new Error("Book not found.");
  saveDb(db);
  return { success: true, message: "Book deleted." };
}

/* ── Optional PHP backend (only if XAMPP is running) ── */

async function detectBackend() {
  if (location.protocol === "file:") return true;
  try {
    const res = await fetch(`${API}/check.php`, { credentials: "same-origin" });
    const data = await res.json();
    return !(res.ok && data.success);
  } catch {
    return true;
  }
}

async function ensureBackend() {
  if (!backendReady) backendReady = detectBackend();
  useLocal = await backendReady;
  return useLocal;
}

async function apiFetch(url, options = {}) {
  await ensureBackend();

  if (useLocal) {
    const body = options.body ? JSON.parse(options.body) : {};
    if (url.includes("signup.php")) return localSignup(body);
    if (url.includes("login.php")) return localLogin(body);
    if (url.includes("reset.php")) return localReset(body);
    if (url.includes("session.php")) return localSession();
    if (url.includes("books.php?meta=1")) return localMeta();
    if (url.includes("books.php") && options.method === "DELETE") {
      const id = parseInt(new URL(url, location.href).searchParams.get("id"), 10);
      return localDeleteBook(id);
    }
    if (url.includes("books.php") && options.method === "PUT") return localSaveBook(body, true);
    if (url.includes("books.php") && options.method === "POST") return localSaveBook(body, false);
    if (url.includes("books.php")) {
      const q = new URL(url, location.href).searchParams.get("search") || "";
      return localBooks(q);
    }
    return { success: true };
  }

  let res;
  try {
    res = await fetch(url, {
      headers: { "Content-Type": "application/json", ...(options.headers || {}) },
      credentials: "same-origin",
      ...options,
    });
  } catch {
    useLocal = true;
    return apiFetch(url, options);
  }

  const text = await res.text();
  let data = {};
  try {
    data = text ? JSON.parse(text) : {};
  } catch {
    useLocal = true;
    return apiFetch(url, options);
  }
  if (!res.ok) throw new Error(data.message || `Request failed (${res.status}).`);
  return data;
}

/* ── Page actions ── */

async function login() {
  try {
    const data = await apiFetch(`${API}/login.php`, {
      method: "POST",
      body: JSON.stringify({
        email: document.getElementById("loginEmail").value.trim(),
        password: document.getElementById("loginPassword").value,
      }),
    });
    showToast(data.message);
    setTimeout(() => (window.location.href = "books.html"), 800);
  } catch (err) {
    showToast(err.message, true);
  }
}

async function signup() {
  try {
    const data = await apiFetch(`${API}/signup.php`, {
      method: "POST",
      body: JSON.stringify({
        full_name: document.getElementById("signupName").value.trim(),
        email: document.getElementById("signupEmail").value.trim(),
        password: document.getElementById("signupPassword").value,
      }),
    });
    showToast(data.message);
    setTimeout(() => (window.location.href = "books.html"), 800);
  } catch (err) {
    showToast(err.message, true);
  }
}

async function resetPassword() {
  try {
    const data = await apiFetch(`${API}/reset.php`, {
      method: "POST",
      body: JSON.stringify({ email: document.getElementById("resetEmail").value.trim() }),
    });
    showToast(data.message);
  } catch (err) {
    showToast(err.message, true);
  }
}

async function logout() {
  if (useLocal) localLogout();
  else await fetch(`${API}/logout.php`, { credentials: "same-origin" }).catch(() => {});
  window.location.href = "login.html";
}

async function initBooksPage() {
  try {
    await ensureBackend();
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
      await apiFetch(`${API}/books.php`, { method: "PUT", body: JSON.stringify({ id: editId, ...payload }) });
      showToast("Book updated");
    } else {
      await apiFetch(`${API}/books.php`, { method: "POST", body: JSON.stringify(payload) });
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
