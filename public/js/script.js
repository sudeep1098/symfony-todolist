document.addEventListener("DOMContentLoaded", function () {
  document
    .querySelectorAll('button[data-bs-target="#notemodal"]')
    .forEach((button) => {
      button.addEventListener("click", function () {
        var todoId = this.value;
        console.log(todoId);
        document.getElementById("todoid").value = todoId;
      });
    });
  document
    .querySelectorAll('button[data-bs-target="#editNoteModal"]')
    .forEach((button) => {
      button.addEventListener("click", function () {
        var noteId = this.value;
        console.log(noteId);
        //   document.getElementById("noteId").value = noteId;
      });
    });
});
