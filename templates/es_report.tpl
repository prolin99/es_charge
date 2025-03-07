<style>
.bgsuccess {
    background-color: #198754;
}
.bgwarning {
    background-color: #FFC107;
}
.bginfo {
    background-color: #0DCAF0;
}

</style>
<{$toolbar}>

    <form  id='frm' class="form-inline" action="report.php"  method='post' >
      	<span><label>繳費項目：</label></span>
        <{html_options name=item_id options=$data.item_list selected=$data.seletc_item class='form-control'  onchange="submit();"}>
     <{if ($data.seletc_item ) }>
  	<{if ($data.admin) }>

      <div class='form-group row col-10'>
    	<!-- 管理者可處理全校學生    -->
      <div class="col-6">
    	 管理繳費班級：
       <{html_options name='admin_class_id' options=$data.class_list selected=$data.class_id class='form-control'   onchange="submit();"}>
       </div>
       <div class="col-3">
    	 <span class="alert alert-danger" title='可修改全校資料，無時限。' >管理員權限!!!!</span>
       </div>
       <div class="col-3">
    	 <a class="btn btn-success"  href="report_list.php?do=excel&show=all&item_id=<{$data.seletc_item}>&class_id=<{$data.class_id}>">匯出 EXCEL</a>
       </div>
      </div> 

    	<{else}>

    	 <a class="btn btn-success"  href="report_list.php?do=excel&show=all&item_id=<{$data.seletc_item}>">匯出 EXCEL</a>
    	<{/if}>

    <{/if}>
   </form>
<{if ($data.class_id )  }>
<{if ($data.seletc_item ) }>
	<{if (!$data.inTime ) }>
	<div class="alert alert-danger">已過填報時限！</div>
	<{/if}>
 	<!--            printableArea     開始              -->
	<div id='printableArea' class='printableArea'>
	<h3><{$data.class_list_c[$data.class_id]}>  班上繳費細項列表(收費人數：<{$data.selected_count}>人，在籍人數：<{$data.students_count}>人)</h3>
     <{* 多餘繳費人記錄 *}>
     <{if ($data.out_student)   }>
     <div class="alert alert-danger">
        <{foreach  key=stud_sn item=s_stud   from= $data.out_student }>
                <p>繳費記錄中： <{$s_stud.student_sn}> 學生: <{$s_stud.rec_name}>已不在最新的學生名冊中！是否已轉出？
                <{if ($data.inTime ) }>
                    <a class="btn btn-danger"  href="javascript:if(confirm('確定學生<{$s_stud.rec_name}>已轉出，在這次收費記錄中已無需繳費?'))location='index.php?mode=del&class_id=<{$s_stud.class_id}>&item_id=<{$s_stud.item_id}>&id=<{$s_stud.record_id}>&sn=<{$s_stud.student_sn}>'">刪除</a>
                <{/if}>
              </p>
         <{/foreach}>
     </div>
     <{/if}>



	<table class="table table-bordered" >
 	<tr>
      <th scope='col'>姓名</th>
      <{foreach  key=detail_key item=detail_val   from= $data.detail_list }>
        <th scope='col' ><{$detail_val}></th>
      <{/foreach}>
      <th scope='col' >小計</th>
    </tr>

  <{foreach  key=key item=stud   from= $data.students }>
    <{if ($data.selected[$stud.stud_id].selected) }>
    <{if ($data.no_account[$key]) }>
    <tr class='alert alert-info' title='無扣款帳號'>
    <{else}>
    <tr >
    <{/if}>
      <{if ($data.selected[$stud.stud_id].in_bank) }>
      <th ><span class="in_bank" id='icon_<{$stud.stud_id}>_<{$data.seletc_item}>' data='1'><span class=" fa fa-shopping-cart" title="銀行扣款"></span></span>(<{$stud.class_sit_num}>)<{$stud.name}></th>
      <{else}>
      <td ><span class="in_bank"  id='icon_<{$stud.stud_id}>_<{$data.seletc_item}>' data='0'>
          <span class='badge badge-danger'>
              <span class=" fa fa-user" title="自行繳費"></span></span></span>
              (<{$stud.class_sit_num}>)<{$stud.name}></td>
      <{/if}>
       		<!--            繳費金額(有申請減免                   -->
      <{foreach  key=detail_key item=detail_val   from= $data.detail_list }>
         <{if ($data.decase_list_array[$stud.stud_id].dollar[$detail_key]) }>
         <td ><span class="bginfo badge badge-primary bg-info "><{$data.pay_list[$stud.stud_id][$detail_key]}></span></td>
         <{else}>
        <td ><{$data.pay_list[$stud.stud_id][$detail_key]}></td>
        <{/if}>
      <{/foreach}>
      <td><{$data.pay_list[$stud.stud_id].each}></td>
     </tr>
    <{/if}>
  <{/foreach}>
  </table>
  </div>  <!--            printableArea     結束              -->
  <!--            自行繳費                   -->
 <{if ($data.self_pay ) }>
	<h3>上一期自行繳費名單提醒</h3>
	<{foreach  key=key item=stud   from= $data.self_pay }>
	<{$data.students[$stud].name}> ,
	<{/foreach}>
<{/if}>
  <div class="alert alert-success"  id='frm'>
  點選最左方圖示，切換是否自動扣款或自行繳費。
  <{if ($DEF.bank_account_use) }>
  <br />無扣款帳號，會出現顏色標記。如要參加扣款請先提供相關資料給出納。
  <{/if}>
  </div>
<{/if}>


 <{if ($data.inTime ) }>
<script>
    //更改是否使用銀行扣款----------------------------------------------------------------------------------
  $(document).on("click", ".in_bank", function(){
    	var div_id = $(this).attr("id")  ;
    	var in_bank = $(this).attr("data")  ;

    	if (in_bank=='1') {

    	 	$('#' + div_id).html( '<span class="badge badge-danger"><span class=" fa fa-user" title="自行繳費"></span></span>') ;
    	 	$('#' + div_id).attr("data","0") ;
    	}else {
    		$('#' + div_id).html( '<span class=" fa fa-shopping-cart" title="銀行扣款"></span>') ;
    		$('#' + div_id).attr("data", "1") ;
    	}
    	in_bank_change(div_id) ;


   });

      var in_bank_change=function( id ){
      //alert(id) ;
             var URLs="ajax_inbank_change.php?id=" + id ;

            $.ajax({
                url: URLs,
                type:"GET",
                dataType:'text',

                success: function(msg){
                   // alert(msg);
                },

                 error:function(xhr, ajaxOptions, thrownError){
                    alert('error:' + xhr.status);
                    alert(thrownError);
                 }
           })
        }

</script>

<{/if}>
<{else}>
  <h4>未選擇繳費項目或非級任身份，無法使用！</h4>
 <{/if}>
