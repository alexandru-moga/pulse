document.addEventListener('DOMContentLoaded', () => {
    fetch('https://api.hackclub.com/v1/ysws')
      .then(response => response.json())
      .then(projects => {
        const container = document.querySelector('.ysws-preview');
        projects.slice(0, 3).forEach(project => {
          const card = document.createElement('div');
          card.className = 'project-card';
          card.innerHTML = `
            <h3>${project.name}</h3>
            <p>${project.description}</p>
            <a href="/ysws/${project.id}">View Project</a>
          `;
          container.appendChild(card);
        });
      });
  
    fetch('/api/stats')
      .then(response => response.json())
      .then(stats => {
        document.getElementById('member-count').textContent = stats.members;
        document.getElementById('project-count').textContent = stats.projects;
      });
  });
  