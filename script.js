function showToast(msg){
const el=document.getElementById("toast");
if(el){el.textContent=msg;el.style.display="block";setTimeout(()=>el.style.display="none",2000);}
}

function toggleTheme(){
  document.body.classList.toggle("light-theme");
  const icon=document.getElementById("themeIcon");
  if(icon) icon.textContent=document.body.classList.contains("light-theme")?"☀️":"🌙";
}

function loginAlert(){showToast(" Login ");}
function signupAlert(){showToast("Account created");}
function resetAlert(){showToast("Reset link sent");}

let books=[];
let editIndex=null;

function loadBooks(){
books=JSON.parse(localStorage.getItem("books"))||[];
renderBooks(books);
}

function addBook(){
const title=document.getElementById("bookTitle").value;
const author=document.getElementById("bookAuthor").value;
if(!title||!author) return;

if(editIndex!==null){
books[editIndex]={title,author};
editIndex=null;
showToast("Book updated");
}else{
books.push({title,author});
showToast("Book added");
}

localStorage.setItem("books",JSON.stringify(books));
renderBooks(books);

document.getElementById("bookTitle").value="";
document.getElementById("bookAuthor").value="";
}

function renderBooks(list){
const grid=document.getElementById("bookGrid");
if(!grid) return;

grid.innerHTML="";

list.forEach((book,index)=>{
const div=document.createElement("div");
div.className="book-card";

div.innerHTML=`
<h3>${book.title}</h3>
<p>${book.author}</p>
<button onclick="editBook(${index})">Edit</button>
<button onclick="deleteBook(${index})">Delete</button>
`;

grid.appendChild(div);
});
}

function editBook(i){
const b=books[i];
document.getElementById("bookTitle").value=b.title;
document.getElementById("bookAuthor").value=b.author;
editIndex=i;
showToast("Editing book...");
}

function deleteBook(i){
books.splice(i,1);
localStorage.setItem("books",JSON.stringify(books));
renderBooks(books);
showToast("Book deleted");
}

function searchBooks(){
const q=document.getElementById("searchInput").value.toLowerCase();
const filtered=books.filter(b=>
 b.title.toLowerCase().includes(q)||
 b.author.toLowerCase().includes(q)
);
renderBooks(filtered);
}
