document.addEventListener('DOMContentLoaded',function(){

	setTimeout(function(){document.querySelector('body').classList.remove('loading');},3000);

    window.addEventListener('load', function(){
      document.querySelector('body').classList.remove('loading');
    });

    if(document.querySelector('.footertop') && !document.querySelector('.footertop > *')){
      document.querySelector('.footertop').classList.add('hide');
    }

    if(document.querySelector('.footerbottom') && !document.querySelector('.footerbottom > *')){
      document.querySelector('.footerbottom').classList.add('hide');
    }
    

    document.querySelectorAll('.pagesidebar .sidemenu li.menu-item-has-children').forEach(function($this){
        
      	$this.addEventListener('click',function(event){
          	event.stopPropagation();
              if(!$this.classList.contains('active')){
                $this.querySelector('ul.sub-menu').classList.add('show');
                $this.classList.add('active');
              }else{
                $this.classList.remove('active');
                $this.querySelector('ul.sub-menu').classList.add('hide');
              }
              
      	});
    });
     if(document.querySelector('.global #trigger')){
      document.querySelector('.global #trigger').addEventListener('click',function(event){
          event.preventDefault();
          event.stopPropagation();
          if(document.querySelector('.global').classList.contains('open')){
            document.querySelector('.global').classList.remove('open');
          }else{
            	document.querySelector('.global').classList.add('open');
            	var event = new CustomEvent("global_opened");
  			document.querySelector('body').dispatchEvent(event);
          }
          
      });
    }


    document.querySelector('body').addEventListener('global_opened',function(){
      	document.querySelector('.pusher').addEventListener('click',function(e){
      		if(document.querySelector('.global').classList.contains('open')){
          		document.querySelector('.global').classList.remove('open');
        	}
      	});
      	document.querySelector('#close_menu_sidebar').addEventListener('click',function(event){
        	event.preventDefault();
        	document.querySelector('.global').classList.remove('open');
      	});
    });

   
    document.querySelectorAll('nav .menu-item .sub-menu').forEach(function(el) {
        if(el.querySelector('.megadrop')){ 
            el.parentElement.style.position='static';
            el.parentElement.parentElement.style.position='static';
            el.classList.add('hasmegamenu');
            var attr = el.querySelector('.megadrop').getAttribute('data-width');
            if (typeof attr != 'undefined' && attr != false) {
              el.style.width=attr;
            }
        }
    });
    if(document.querySelector('#new_searchicon')){
      document.querySelector('#new_searchicon').addEventListener('click',function(event) {
          document.querySelector('body').classList.add('search_active');
      });
    }
    if(document.querySelector('#mobile_searchicon')){
      document.querySelector('#mobile_searchicon').addEventListener('click',function(event) {
          document.querySelector('body').classList.add('search_active');
      });
    }
    if(document.querySelector('#scrolltop')){
      document.querySelector('#scrolltop').addEventListener('click',function(event) {
          window.scrollTo({top:0,left:0,behavior: 'smooth'});
      });
    }
    if(document.querySelector('#searchdiv span')){
      document.querySelector('#searchdiv span').addEventListener('click',function(){
          document.querySelector('body').classList.remove('search_active');
      });
    }
    if(document.querySelector('body').classList.contains('search_active')){
	    document.addEventListener('mouseup',function (e) {
	        var container = document.querySelector('#searchdiv');
	        if (document.querySelector(e.target).getAttribute('id') == 'searchdiv'){
	            document.querySelector('body').classList.remove('search_active');
	        }
	    });
	}

	document.querySelectorAll('body .woocommerce-error').forEach(function(el){
		el.addEventListener('click',function(event){
      		e.classList.add('hide');
      	});
    });
	if(document.querySelector('#more_desc')){
	    document.querySelector('#more_desc').addEventListener('click',function(event) {
	      	event.preventDefault();
	      	event.target.classList.add('hide');
	      	document.querySelector('.full_desc').classList.add('show');

	    });

	    document.querySelector('.course_description #less_desc').addEventListener('click',function(event) {
	      event.preventDefault();
	        document.querySelector('.full_desc').classList.add('hide');
	        document.querySelector('#more_desc').classList.add('show');
	    });
	}

    document.querySelectorAll('#signup_password, #account_password').forEach(function($this){
      var label;
      if($this.classList.contains('form_field')){
        label =  document.querySelector('label[for="signup_password"]');
      }else{
        label =  document.querySelector('label[for="account_password"]');
      }
      $this.addEventListener('keyup',function() {
        
        if(label.querySelector('span')){ 
          label.querySelector('span').innerHTML(checkStrength($this.value,label));
        }else{
        	var p = document.createElement('span');
			p.innerHTML = checkStrength($this.value,label);
			label.appendChild(p);
        }
      });
      	function checkStrength(password,label) {
        	var strength = 0
	        if (password.length < 6) {
		        label.className='';
		        label.classList.add('short');
		        return v4.too_short
	        }
	        if (password.length > 7) strength += 1
	        // If password contains both lower and uppercase characters, increase strength value.
	        if (password.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) strength += 1
	        // If it has numbers and characters, increase strength value.
	        if (password.match(/([a-zA-Z])/) && password.match(/([0-9])/)) strength += 1
	        // If it has one special character, increase strength value.
	        if (password.match(/([!,%,&,@,#,$,^,*,?,_,~])/)) strength += 1
	        // If it has two special characters, increase strength value.
	        if (password.match(/(.*[!,%,&,@,#,$,^,*,?,_,~].*[!,%,&,@,#,$,^,*,?,_,~])/)) strength += 1
	        // Calculated strength value, we can return messages
	        // If value is less than 2
        	if (strength < 2) {
          		label.className='';
          		label.classList.add('weak');
          		return v4.weak
        	} else if (strength == 2) {
          		label.className='';
          		label.classList.add('good');
          		return v4.good
        	} else {
          		label.className='';
          		label.classList.add('strong')
          		return v4.strong
        	}
      	}
    });

    if((document.querySelector('header.sleek') && document.querySelector('header.sleek').classList.contains('transparent')) 
    	|| document.querySelector('header') && document.querySelector('header').classList.contains('generic')){

      	var headerheight = document.querySelector('header').offsetHeight+30;
      	var header = document.querySelector('header');

      	var next;
      	if(document.querySelector('body').classList.contains('page-template-contact-php')){
          	next = 0;
      	}else if(document.querySelector('body').classList.contains('bp-user') && ( document.querySelector('body').classList.contains('p2') || document.querySelector('body').classList.contains('p3') || document.querySelector('body').classList.contains('p4') || document.querySelector('body').classList.contains('modern-theme'))){
        	next = document.querySelector('#item-header');
      	}else if(document.querySelector('body').classList.contains('groups') && document.querySelector('body').classList.contains('single-item') && ( document.querySelector('body').classList.contains('g2') || document.querySelector('body').classList.contains('g3')) || (document.querySelector('body').classList.contains('single-item') && document.querySelector('body').classList.contains('modern-theme') && !document.querySelector('body').classList.contains('g4'))){
          	next = document.querySelector('#item-header');
      	}else if((document.querySelector('body').classList.contains('single-course') && ( document.querySelector('body').classList.contains('c2') || document.querySelector('body').classList.contains('c3') || document.querySelector('body').classList.contains('c5') )) || (document.querySelector('body').classList.contains('single-course') && document.querySelector('body').classList.contains('modern-theme') && !document.querySelector('body').classList.contains('c4'))){
        	next = document.querySelector('#item-header');
      	}else if(document.querySelector('body').classList.contains('activity-permalink')){
      		var p = document.createElement('div');
      		p.setAttribute("id","title")
        	header.appendChild(p);
        	next = document.querySelector('#title');
      	}else{
          	next = header.nextElementSibling;
            
          	if(next.offsetHeight < 10){
            	next = header.nextElementSibling.nextElementSibling;
          	}  
      	}
      	if(next){
        	if(next.querySelector('.wpb_wrapper')){
          		next = next.querySelector('.wpb_wrapper:first');
        	}
        	next.style.paddingTop=headerheight+'px';
        	next.classList.add('light');
      	}

  	}

  	if(document.querySelector('header') && document.querySelector('header').classList.contains('mooc') && document.querySelector('#mooc_searchform')){
      	document.querySelector('#mooc_searchform').click(function(event){
	          if( event.target.type != 'input'){
	              document.querySelector(this).querySelector('.search_form').toggleClass('active');
	          }
      	});
   	}


   	var vibe_header_fix = function(){
          window.addEventListener('scroll',function(event){
            var st = window.pageYOffset;
            
            if(document.querySelector('#headertop')){
	            if(document.querySelector('#headertop').classList.contains('fix')){
	              var headerheight=document.querySelector('header').offsetHeight;
	              if(st > headerheight){
	                document.querySelector('#headertop').classList.add('fixed');
	              }else{
	                document.querySelector('#headertop').classList.remove('fixed');
	              }
	            }
            }

            if(document.querySelector('header') && (document.querySelector('header.sleek') && document.querySelector('header.sleek').classList.contains('fix')) || (document.querySelector('header.generic') && document.querySelector('header.generic').classList.contains('fix'))){

              var header = document.querySelector('header.fix');
              var headerheight=parseInt(document.querySelector('header.fix').offsetHeight);
              var next = '';
              //if(header.classList.contains('transparent'))
                headerheight = headerheight + 30;
              
              if(document.querySelector('body').classList.contains('page-template-contact-php')){
                next = '';
              }else if(document.querySelector('body').classList.contains('bp-user') && ( document.querySelector('body').classList.contains('p2') || document.querySelector('body').classList.contains('p3') || document.querySelector('body').classList.contains('p4'))){
                next = document.querySelector('#item-header');
              }else if((document.querySelector('body').classList.contains('.groups') || document.querySelector('body').classList.contains('.single-item')) && ( document.querySelector('body').classList.contains('g2') || document.querySelector('body').classList.contains('g3'))){
                next = document.querySelector('#item-header');
              }else if((document.querySelector('body').classList.contains('single-course') && ( document.querySelector('body').classList.contains('c2') || document.querySelector('body').classList.contains('c3') || document.querySelector('body').classList.contains('c5')) ) || (document.querySelector('body').classList.contains('single-course') && document.querySelector('body').classList.contains('modern-theme') && !document.querySelector('body').classList.contains('c4'))){
                next = document.querySelector('#item-header');
              }else{
                next = header.nextElementSibling;
                if(next.offsetHeight <10){
                  next = header.nextElementSibling.nextElementSibling;
                }  
              }
              if(next.querySelector('.wpb_wrapper')){
                next = next.querySelector('.wpb_wrapper:first');
              }
              if(st > headerheight){
                document.querySelector('header.fix').classList.add('fixed');
                if(header.classList.contains('fixed')){
                  if(next){
                    next.style.paddingTop=headerheight+'px';
                  }
                }
              }else{
                document.querySelector('header.fix').classList.remove('fixed');
                if(!header.classList.contains('transparent') && !header.classList.contains('generic')){  // case for sleek
                  if(next)
                    next.style.paddingTop=0;
                }
              }


            } // End sleek has class fix

            if(document.querySelector('header') && document.querySelector('header.standard,header.mooc fix')){
              var header = document.querySelector('header.standard,header.mooc fix');
              var headerheight=document.querySelector('header.fix').offsetHeight;
              if(st > headerheight){
                document.querySelector('header.fix').classList.add('fixed');
              }else{
                document.querySelector('header.fix').classList.remove('fixed');
              }
            }

            if(document.querySelector('#scrolltop')){
              if(st > document.documentElement.clientHeight){
                document.querySelector('#scrolltop').classList.add('fix');
              }else{
                document.querySelector('#scrolltop').classList.remove('fix');
              }
            } 
          },{passive:true}); // End scroll event 
    }

    vibe_header_fix();

    
    if(document.querySelector('.vbpcart')){
	  	document.querySelector('.vbpcart').addEventListener('click',function(event){
        if(event.target.parentNode.getAttribute('href')){
          return;
        }
	      	event.preventDefault();
	      	if(event.target.classList.contains('active')){
	      		event.target.classList.remove('active');
	      	}else{
	      		event.target.classList.add('active');
	      	}
	      	if(document.querySelector('.woocart').classList.contains('active')){
	      		document.querySelector('.woocart').classList.remove('active');
	      	}else{
	      		document.querySelector('.woocart').classList.add('active');
	      	}
	    });
    }
  	
    if(document.querySelector('.field-visibility-settings')){
      document.querySelectorAll('.field-visibility-settings').forEach(function(el){
        el.classList.add('hide');
        el.parentNode.querySelector('.field-visibility-settings-toggle').addEventListener('click',function(e){
          e.preventDefault();
          e.target.parentNode.parentNode.querySelector('.field-visibility-settings').classList.toggle('hide');
        });
      });

    }


  	document.addEventListener('resize',function() {
    	document.removeEventListener('scroll');
    	course5_sideblock();
    });

	var course5_sideblock = function(){
	    document.querySelectorAll('.course_header5_sideblock,.fixed_block > .elementor-element-populated').forEach(function(el){

	        var w = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
	        if(w > 768){
	           
	            var cwidth = el.parentElement.offsetWidth;
	            var contentHeight=0;
              var top=0;
              if(document.querySelector('#content')){
                contentHeight = document.querySelector('#content').offsetHeight;
                top = document.querySelector('#content').scrollTop;
              }
              if(document.querySelector('#vibebp_member')){
                contentHeight = document.querySelector('#vibebp_member').offsetHeight;
                top = document.querySelector('#vibebp_member').scrollTop;
              }
              var etop = el.getBoundingClientRect().top;
	            var endheight = top + contentHeight - el.offsetHeight-etop;  
	            el.style.width = cwidth+'px';
	           
	            window.addEventListener('scroll',function(event){
	                var st = window.pageYOffset;

	                if( st < endheight){
                    if(st > etop){
	                    el.style.transform ='translateY('+st+'px)';
                    }else{
                      el.style.transform ='none';
                    }
	                }
	            },false);
	          
	        }else{
	            el.style.width='';

              if(el.querySelector('.course_button_wrapper')){
                window.addEventListener('scroll',function(event){
                    var st = window.pageYOffset;

                    if( st > el.querySelector('.course_button_wrapper').getBoundingClientRect().top){
                      el.querySelector('.course_button_wrapper').classList.add('fix');
                    }else{
                      el.querySelector('.course_button_wrapper').classList.remove('fix');
                    }
                });
              }
	        }
	      
	    });

      
	};
	course5_sideblock();

  window.addEventListener('resize',function(){
    course5_sideblock();
  });

  document.querySelectorAll('.animate').forEach(function(el){
    if(el.classList.contains('loaded')){
      el.classList.add('loaded');
    }
  });
});