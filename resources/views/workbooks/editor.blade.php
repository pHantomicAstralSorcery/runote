@extends('welcome')

@section('content')
@verbatim
<style>
  .editor-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    margin-bottom: 8px;
  }
  .editor-toolbar .btn {
    padding: 0.375rem 0.5rem;
    font-size: 1rem;
  }
  #editor {
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
    min-height: 250px;
    padding: 0.75rem;
    background-color: #fff;
  }
  #editor table { width: 100%; border-collapse: collapse; }
  #editor table th, #editor table td { border:1px solid #adb5bd; padding:4px; }
  .field-placeholder { background: #fff3cd; padding:2px 4px; border:1px dashed #ffecb5; }

.editor-toolbar select {
  width: auto !important;
  max-width: 100px;
}

</style>
<div class="container">
<div class="editor-toolbar btn-toolbar" role="toolbar">
  <div class="btn-group me-2">
    <button type="button" class="btn btn-outline-secondary" onclick="exec('bold')" title="Жирный (Ctrl+B)"><b>B</b></button>
    <button type="button" class="btn btn-outline-secondary" onclick="exec('italic')" title="Курсив (Ctrl+I)"><i>I</i></button>
    <button type="button" class="btn btn-outline-secondary" onclick="exec('underline')" title="Подчёркивание"><u>U</u></button>
    <button type="button" class="btn btn-outline-secondary" onclick="exec('strikeThrough')" title="Зачёркивание"><s>S</s></button>
  </div>

  <div class="btn-group me-2">
    <button type="button" class="btn btn-outline-secondary" onclick="exec('justifyLeft')" title="Влево">L</button>
    <button type="button" class="btn btn-outline-secondary" onclick="exec('justifyCenter')" title="Центр">C</button>
    <button type="button" class="btn btn-outline-secondary" onclick="exec('justifyRight')" title="Вправо">R</button>
    <button type="button" class="btn btn-outline-secondary" onclick="exec('justifyFull')" title="По ширине">J</button>
  </div>

  <select class="form-select form-select-sm me-2" onchange="exec('fontSize', this.value)" title="Размер шрифта">
    <option value="">Размер</option>
    <option value="2">12pt</option>
    <option value="3">14pt</option>
    <option value="4">16pt</option>
    <option value="5">18pt</option>
    <option value="6">24pt</option>
  </select>

  <div class="btn-group me-2">
    <button type="button" class="btn btn-outline-secondary" onclick="exec('insertUnorderedList')" title="Маркированный список">• List</button>
    <button type="button" class="btn btn-outline-secondary" onclick="exec('insertOrderedList')" title="Нумерованный список">1. List</button>
  </div>

  <div class="btn-group me-2">
    <button type="button" class="btn btn-outline-secondary" onclick="insertLink()" title="Вставить ссылку">🔗</button>
    <button type="button" class="btn btn-outline-secondary" onclick="insertTable()" title="Вставить таблицу">▦</button>
  </div>

  <div class="btn-group me-2">
    <button type="button" class="btn btn-outline-secondary" onclick="triggerImage()" title="Вставить изображение">🖼️</button>
    <input type="file" id="imgUploader" accept="image/*" style="display:none">
  </div>

  <div class="btn-group me-2">
    <button type="button" class="btn btn-outline-secondary" onclick="insertField()" title="Вставить поле для ответа">✎ Field</button>
  </div>

  <div class="btn-group">
    <button type="button" class="btn btn-outline-secondary" onclick="undo()" title="Отменить (Ctrl+Z)">↺</button>
    <button type="button" class="btn btn-outline-secondary" onclick="redo()" title="Вернуть">↻</button>
  </div>
</div>

<div id="editor" contenteditable="true"></div>
</div>
<script>
  const editor = document.getElementById('editor');
  const imgUploader = document.getElementById('imgUploader');
  let historyStack = [], historyPos = -1, maxHistory = 50;

  function saveState(){
    historyStack = historyStack.slice(0, historyPos+1);
    historyStack.push(editor.innerHTML);
    if(historyStack.length>maxHistory) historyStack.shift();
    historyPos = historyStack.length-1;
  }

  function exec(cmd, val=null){
    document.execCommand(cmd, false, val);
    saveState();
    editor.focus();
  }

  function insertTable(){
    saveState();
    let rows=+prompt('Строки','2'), cols=+prompt('Столбцы','2');
    if(rows>0 && cols>0){
      let tbl='<table>';
      for(let r=0;r<rows;r++){
        tbl+='<tr>';
        for(let c=0;c<cols;c++) tbl+='<td>&nbsp;</td>';
        tbl+='</tr>';
      }
      tbl+='</table>';
      exec('insertHTML', tbl);
    }
  }

  function insertLink(){
    let url=prompt('Введите URL','https://');
    if(url) exec('createLink', url);
  }

  function triggerImage(){
    imgUploader.click();
  }
  imgUploader.onchange = ()=>{
    let file=imgUploader.files[0];
    if(file){
      let reader=new FileReader();
      reader.onload=e=>{ exec('insertImage', e.target.result); };
      reader.readAsDataURL(file);
    }
  };

  function insertField(){
    let id=prompt('ID поля (число)','1');
    if(id) {
      saveState();
      exec('insertHTML', '<span class="field-placeholder">{{ field:'+id+' }}</span>');
    }
  }

  function undo(){
    if(historyPos>0) editor.innerHTML = historyStack[--historyPos];
  }
  function redo(){
    if(historyPos<historyStack.length-1) editor.innerHTML = historyStack[++historyPos];
  }

  editor.addEventListener('input', saveState);
  document.addEventListener('DOMContentLoaded', ()=>saveState());
</script>
@endverbatim
@endsection
