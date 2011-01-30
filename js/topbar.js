$(document).ready(hideMenu);
$(document).ready(loadProjects);

function showMenu()
{
  document.getElementById('dropdown').style.top = '40px';
  this.removeEventListener('click', showMenu, true);
  this.addEventListener('click', hideMenu, false);
}

function hideMenu()
{
  document.getElementById('dropdown').style.top = '-999px';
  document.getElementById('session').removeEventListener('click', hideMenu, false);
  document.getElementById('session').addEventListener('click', showMenu, false);
}


function loadProjects()
{
  jQuery.getJSON('/js/projects.json', function(data)
  {
    var dropbox = $('.user-dropdown');
    dropbox.html('');
    var count = 0;
    var repositories = [];
    for(var i=0,m=data['repositories'].length;i<m;i++)
    {
      var repository = data['repositories'][i];
      if(!repository['fork'] && repository['homepage'])
      {
        count++;
        dropbox.append('<li><a href="'+repository['url']+'"><strong>'+repository['name']+'</strong><br/><span class="desc">'+repository['description']+'</span></a></li>');
      }
    }
    console.log('appended: '+count+'/'+data['repositories'].length);
  });
}
