(function(wp){
  if(!wp || !wp.blocks || !wp.element || !wp.components) return;
  if(wp.blocks.getBlockType('wpbb/hero')) return;
    var e=wp.element.createElement;
    var Fragment=wp.element.Fragment;
    var TextControl=wp.components.TextControl;
    var TextareaControl=wp.components.TextareaControl;
    var SelectControl=wp.components.SelectControl;
    var Notice=wp.components.Notice;
    var InspectorControls=wp.blockEditor && wp.blockEditor.InspectorControls;
    var PanelBody=wp.components.PanelBody;
    wp.blocks.registerBlockType('wpbb/hero',{
      apiVersion:2,
      title:'Hero (Zolei theme compatibility)',
      description:'Theme-side editor compatibility for the WP BBuilder Hero block. The live Zolei homepage reads these values directly.',
      icon:'cover-image',
      category:'design',
      attributes:{
        title:{type:'string',default:''},
        text:{type:'string',default:''},
        buttonText:{type:'string',default:''},
        buttonUrl:{type:'string',default:''},
        theme:{type:'string',default:'dark'},
        titleSize:{type:'string',default:'display-3'}
      },
      supports:{html:false,reusable:true},
      edit:function(props){
        var a=props.attributes||{};
        var set=props.setAttributes;
        var controls=e('div',{className:'zole-theme-hero-compat',style:{padding:'24px',border:'1px solid #dcdcde',borderRadius:'10px',background:a.theme==='dark'?'#103c2d':'#fff'}},
          e('div',{style:{color:a.theme==='dark'?'#fff':'#1d2327'}},
            e('strong',{style:{display:'block',marginBottom:'12px'}},'Zolei Homepage Hero'),
            e(TextControl,{label:'Title',value:a.title||'',onChange:function(v){set({title:v});}}),
            e(TextareaControl,{label:'Text',value:a.text||'',rows:4,onChange:function(v){set({text:v});}}),
            e(TextControl,{label:'Button text',value:a.buttonText||'',onChange:function(v){set({buttonText:v});}}),
            e(TextControl,{label:'Button URL',value:a.buttonUrl||'',onChange:function(v){set({buttonUrl:v});}})
          )
        );
        var inspector=InspectorControls?e(InspectorControls,null,
          e(PanelBody,{title:'Hero appearance',initialOpen:true},
            e(SelectControl,{label:'Theme',value:a.theme||'dark',options:[{label:'Dark',value:'dark'},{label:'Light',value:'light'}],onChange:function(v){set({theme:v});}}),
            e(SelectControl,{label:'Title size',value:a.titleSize||'display-3',options:[{label:'Large',value:'display-2'},{label:'Default',value:'display-3'},{label:'Compact',value:'display-4'}],onChange:function(v){set({titleSize:v});}}),
            e(Notice,{status:'info',isDismissible:false},'This fallback is supplied only by the Zolei theme. If WP BBuilder registers its own Hero block, the theme does not replace it.')
          )
        ):null;
        return e(Fragment,null,inspector,controls);
      },
      save:function(){ return null; }
    });
})(window.wp);
