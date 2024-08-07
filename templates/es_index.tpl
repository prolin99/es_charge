<{$toolbar}>
<{if ($data.class_id )  }>
<div class='container'>
     <form class="form-inline" action="index.php"  method='post'>
       <div class="form-group">
      	<label for="item_id">繳費項目：</label>
        <{html_options name='item_id' options=$data.item_list selected=$data.seletc_item class='form-control' onchange="submit();"}>
      </div>
   </form>
</div>

<{if ($data.seletc_item )  }>
<div class="container" >
    <form   action="index.php"  method='post'>
    <{if ($data.admin) }>
    	<!-- 管理者可處理全校學生    -->
    <div class='row alert alert-danger'>
      <div class="col-1">
       <label for="admin_class_id">班級</label>
       </div>
      <{html_options name='admin_class_id' options=$data.class_list_c selected=$data.class_id class='col-2 form-control' onchange="submit();"}> 班級學生繳費名單(收費人數：<{$data.selected_count}>人，在籍人數：<{$data.students_count}>人)

      <div class='col-9'>

    	<button type="submit" class="btn btn-primary" name='act_grade' value='act_grade' >開列全學年學生</button>
    	<button type="submit" class="btn btn-danger"  name='act_grade_remove'  value="act_grade_remove" onclick='return confirm("確定要移除?\n\nPS.繳費紀錄亦會一併被刪除")' >清空全學年學生</button>
      </div>
      </div>


    <{else}>
    	<legend><{$data.class_list_c[$data.class_id]}>繳費名單(收費人數：<{$data.selected_count}>人，在籍人數：<{$data.students_count}>人)</legend>
    <{/if}>

    <div class='row'>
    <{foreach  key=key item=stud   from= $data.students }>

        <{if ($data.selected[$stud.stud_id].selected) }>  <{* 有開列 *}>
          <div class='col-3'><label>■(<{$stud.class_sit_num}>)<{$stud.name}></label></div>
        <{else}>
          <div class='col-3'><label><input type='checkbox' name='selected_stud[]' value='<{$stud.stud_id}>_<{$stud.class_sit_num}>' id='stud_selected'>(<{$stud.class_sit_num}>)<{$stud.name}></label></div>
        <{/if}>

     <{/foreach }>
     </div>

        <div class="alert alert-warning" role="alert">基本上全班學生都開列，除非該學生在這次的轉帳的各種項目費用都無關才不需開列。</div>
         <span class='col-2'><label><input type='checkbox' name='CheckAll'  id='CheckAll'>全選</label> </span>
         <input type="hidden" name="class_id" value="<{$data.class_id}>" />
         <input type="hidden" name="item_id" value="<{$data.seletc_item}>" />
         <button type="submit" class="btn btn-success" name='act_add' value='act_add' >開列選擇的學生</button>
         <button type="submit" class="btn btn-danger"  name='act_remove'  value="act_remove" onclick='return confirm("確定要移除?\n\nPS.繳費紀錄亦會一併被刪除")' >清空本班級開列的名單</button>
         </form>
     </div>

     <{* 多餘繳費人記錄 *}>
     <{if ($data.out_student)   }>
     <div class="alert alert-danger">
        <{foreach  key=stud_sn item=s_stud   from= $data.out_student }>
                <p>繳費記錄中： <{$s_stud.student_sn}> 學生: <{$s_stud.rec_name}>已不在最新的學生名冊中！是否已轉出？<a class="btn btn-danger"  href="javascript:if(confirm('確定學生<{$s_stud.rec_name}>已轉出，在這次收費記錄中已無需繳費?'))location='index.php?mode=del&class_id=<{$s_stud.class_id}>&item_id=<{$s_stud.item_id}>&id=<{$s_stud.record_id}>&sn=<{$s_stud.student_sn}>'">刪除</a></p>
         <{/foreach}>
     </div>
     <{/if}>

<{/if}>
<script>
	//全選
	$(document).on("click", "#CheckAll", function(){
                //var chk = $(this).attr('checked');
                var chk = $(this).prop('checked');
                //alert(chk) ;
                if  (chk)
		$('div').find(':checkbox').prop('checked',  true );
	   else
		$('div').find(':checkbox').prop('checked' ,false);
    })

</script>

<{else}>
  <h4>非級任身份，無法使用！</h4>
 <{/if}>
