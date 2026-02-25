function showToast(msg){
}

function loginAlert(){showToast(" Login ");}
function signupAlert(){showToast("Account created");}
function resetAlert(){showToast("Reset link sent");}

let books=[];
let editIndex=null;

function loadBooks(){
books=JSON.parse(localStorage.getItem(" Books "))||[];
renderBooks(books);
}

function addBook(){
const title=document.getElementById(" BookTitle ").value;
const author=document.getElementById(" BookAuthor ").value;
if(!title||!author) return;

if(editIndex!==null){
books[editIndex]={title,author};
editIndex=null;
showToast("Book updated");
}else{
books.push({title,author});
showToast("Book added");
}

localStorage.setItem("Books",JSON.stringify(books));
renderBooks(books);

document.getElementById("BookTitle").value="";
document.getElementById("BookAuthor").value="";
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
document.getElementById("BookTitle").value=b.title;
document.getElementById("BookAuthor").value=b.author;
editIndex=i;
showToast("Editing book...");
}

function deleteBook(i){
books.splice(i,1);
localStorage.setItem("Books",JSON.stringify(books));
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
