const projects = [
  {
    id: "P-001",
    title: "Integración de pagos",
    owner: "Fintech",
    created: "2025-10-12",
    promoted: false,
    promotedAt: null,
  },
  {
    id: "P-002",
    title: "Refactor API pública",
    owner: "Backend",
    created: "2025-09-30",
    promoted: true,
    promotedAt: "2025-10-28",
  },
  {
    id: "P-003",
    title: "Nueva UI Dashboard",
    owner: "Frontend",
    created: "2025-10-05",
    promoted: false,
    promotedAt: null,
  },
];

const projectList = document.getElementById("projectList");
const countSpan = document.getElementById("count");
const searchInput = document.getElementById("search");

function renderProjects(list) {
  projectList.innerHTML = "";
  countSpan.textContent = list.length;

  if (list.length === 0) {
    projectList.innerHTML =
      "<p style='color:#9ca3af;text-align:center;padding:2rem;'>No hay proyectos que coincidan.</p>";
    return;
  }

  list.forEach((p) => {
    const div = document.createElement("div");
    div.className = "project";

    div.innerHTML = `
      <div class="info">
        <div class="icon">${p.owner[0]}</div>
        <div class="text">
          <strong>${p.title}</strong><br>
          <small>${p.owner} • ${p.id}</small>
        </div>
      </div>
      <div class="status">
        <div class="dates">
          <small>Creado: ${p.created}</small><br>
          ${p.promoted ? `<small>Promovido: ${p.promotedAt}</small>` : ""}
        </div>
        <span class="badge ${p.promoted ? "promoted" : "waiting"}">
          ${p.promoted ? "✔ Promovido" : "⏳ En espera"}
        </span>
      </div>
    `;

    projectList.appendChild(div);
  });
}

function filteredProjects() {
  const query = searchInput.value.toLowerCase();
  return projects.filter(
    (p) =>
      p.title.toLowerCase().includes(query) ||
      p.owner.toLowerCase().includes(query) ||
      p.id.toLowerCase().includes(query)
  );
}

searchInput.addEventListener("input", () => {
  renderProjects(filteredProjects());
});

renderProjects(projects);
