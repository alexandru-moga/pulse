// Load YSWS projects from Hack Club API or local data
document.addEventListener('DOMContentLoaded', function() {
    const projectsGrid = document.querySelector('.projects-grid');
    
    // Simulated data - replace with actual API call
    const projects = [
      {
        id: 1,
        name: "Community Dashboard",
        description: "Track project progress, member contributions, and club milestones.",
        image: "images/project-example.jpg"
      },
      {
        id: 2,
        name: "Hardware Hacking",
        description: "Build physical computing projects with Arduino and Raspberry Pi.",
        image: "images/project-hardware.jpg"
      },
      {
        id: 3,
        name: "Mobile App Development",
        description: "Create cross-platform mobile apps with React Native.",
        image: "images/project-mobile.jpg"
      }
    ];
    
    // Render projects to the page
    projects.forEach(project => {
      const projectCard = document.createElement('div');
      projectCard.className = 'project-card';
      
      projectCard.innerHTML = `
        <img src="${project.image}" alt="${project.name}" class="project-img">
        <div class="project-details">
          <h3 class="project-title">${project.name}</h3>
          <p class="project-description">${project.description}</p>
          <a href="/projects/${project.id}" class="button project-button">View Project</a>
        </div>
      `;
      
      projectsGrid.appendChild(projectCard);
    });
  });
  