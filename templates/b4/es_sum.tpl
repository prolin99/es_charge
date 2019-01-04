
<{$toolbar}>


<{if ($data.class_id )  }>
    <form id ='frm' class="form-inline" action="sum.php"  method='post'>
      	<span><label>繳費項目：</label>
        </span><{html_options name=item_id options=$data.item_list selected=$data.seletc_item class='form-control' onchange="submit();"}>
      	 <{if ($data.seletc_item ) }>
      	<a class="btn btn-success"  href="sum_report.php?do=excel&show=all&item_id=<{$data.seletc_item}>">匯出 EXCEL</a>
      	<{/if}>
   </form>

<{if ($data.seletc_item ) }>

<div id='printableArea' class='printableArea'>
<h3><{$data.class_list_c[$data.class_id]}> 班級細目統計，收費人數：<{$data.class_source_pay.man}>人(學籍人數：<{$data.students_count}>人)</h3>

     <{if ($data.out_student)   }> <{* 多餘繳費人記錄 *}>
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

 <table class="table table-bordered">
 	<tr><td>收費細目</td><td>細目額</td><td>班級金額</td><td>減免數</td><td>減免額</td><td>應收合計</td> </tr>
   <{foreach  key=detail_key item=detail_val   from= $data.detail_list }>
 	<tr><td><{$detail_val}></td><td><{$data.detail_dollar.pay[$detail_key]}></td><td><{$data.class_source_pay.detail[$detail_key]}></td><td><{$data.class_decrease.man[$detail_key]}></td><td><{$data.class_decrease.sum[$detail_key]}></td><td><{$data.class_source_pay.end_detail[$detail_key]}></td> </tr>
   <{/foreach}>
   <tr><td>總計</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td><{$data.class_source_pay.end_sum}></td> </tr>
 </table>
 <p>導師簽章：</p>

</div>
<{/if}>

<{else}>
  <h4>非級任身份，無法使用！</h4>
 <{/if}>
