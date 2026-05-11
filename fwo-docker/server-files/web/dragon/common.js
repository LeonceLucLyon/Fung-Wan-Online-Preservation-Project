function trim(s){return s.replace(/^\s+/,"").replace(/\s+$/,"")}
function trimo(o){o.value=trim(o.value)}
function isnum(s){return !isNaN(s)}
function iscc(s){return s.match(/^\d{16}$/)?true:false}
function ispp(s){return s.match(/^\d{13}$/)?true:false}
function ispin(s){return s.match(/^\d{6}$/)?true:false}
function len(s){return s.length}
function isemail(s){
 //return true
 var r1=/(@.*@)|(\.\.)|(@\.)|(\.@)|(^\.)/
 var r2=/^.+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,3}|[0-9]{1,3})(\]?)$/
 return(!r1.test(s) && r2.test(s))
// return s.match(/^\w+(\.\w+)*@\w+-*\w+(\.\w+)+$/)?true:false
}
function islen(s,u,l){
 if(!l)l=0
 var r=new RegExp("^.{"+l+","+u+"}$")
 return r.test(s)
}
function isdate(s){
 if(!s.match(/^\d{1,2}\/\d{1,2}\/\d{4}$/))return 0
 var dt=extractdt(s)
 if(dt.year<1900)return false
 if(dt.month<1||dt.month>12)return false
 switch(dt.month){
  case 4:case 6:case 9:case 11:
   ld=30
   break
  case 2:
   ld=28
   if(dt.year%4==0&&dt.year%100!=0)ld++
   break
  default:
   ld=31
 }
 return dt.day<=ld&&dt.day>0
}
function dtcmp(y1,m1,d1,y2,m2,d2){
  if(y1>y2)return 1
  if(y1<y2)return -1  
  if(m1>m2)return 1
  if(m1<m2)return -1  
  if(d1>d2)return 1
  if(d1<d2)return -1
  return 0
}
function gettoday(){
 var dt=new Date()
 y=dt.getFullYear()
 m=dt.getMonth()
 d=dt.getDate()
 return {year:y,month:++m,day:d}
}
function extractdt(s){
 dt=s.split('/')
 y=parseInt(dt[2])
 m=parseInt(dt[1])
 d=parseInt(dt[0])
 return {year:y,month:m,day:d}
}
function sel_radio(c,v){
 for(var n=0; n<c.length; n++)
  if(c[n].value==v){
   c[n].checked=1
   break
  }
}
function sel_combo(c,v){
 for(var i=0;i<c.length;i++)
  if(c[i].value==v){
   c[i].selected=1
   break
  }
}
function trim_inputs(a){
 for(var i=0;i<a.length;i++)
  trimo(a[i])
}

//for text box
function ensure_not_empty(o,s){
  if(o.value.length)return 1
  o.focus()
  alert('Please enter '+s+'.')
  return 0
}
//for combo box
function ensure_select(o,s){
 if(o[o.selectedIndex].value.length)return 1
 o.focus()
 alert('Please select a valid entry for '+s+'.')
 return 0
}
//for radio btn
function ensure_choose(o,s){
 for(var i=0;i<o.length;i++)
  if(o[i].checked)return 1
 o[0].focus()
 alert('Please choose a valid option for '+s+'.')
 return 0
}
//for chk box
function ensure_tick(o,s,n,m){
 var c=0
 for(var i=0;i<o.length;i++)
  if(o[i].checked)c++
 if(m&&c>m){
  o[0].focus()
  alert('Please tick not more than '+m+' for '+s+'.')
  return 0
 }
 if(c>=n)return 1
 o[0].focus()
 alert('Please tick not less than '+n+' for '+s+'.')
 return 0
}