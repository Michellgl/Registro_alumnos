package api_registro.api;

import org.springframework.boot.SpringApplication;
import org.springframework.boot.autoconfigure.SpringBootApplication;
import org.springframework.web.bind.annotation.*; // Importante para las rutas
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;
import org.springframework.http.ResponseEntity;
import jakarta.persistence.*;
import lombok.Data;
import java.util.List;
import java.util.Optional;

@SpringBootApplication
public class ApiApplication {
	public static void main(String[] args) {
		SpringApplication.run(ApiApplication.class, args);
	}
}

// --- CONTROLADOR ---
@RestController
@RequestMapping("/api")
@CrossOrigin(origins = "*") // Permite que PHP se conecte
class AlumnoController {

	private final AlumnoRepository repositorio;

	public AlumnoController(AlumnoRepository repositorio) {
		this.repositorio = repositorio;
	}

	// 1. OBTENER TODOS (GET)
	@GetMapping("/alumnos")
	public List<Alumno> obtenerAlumnos() {
		return repositorio.findAll();
	}

	// 2. GUARDAR O EDITAR (POST)
	@PostMapping("/alumnos")
	public Alumno guardarAlumno(@RequestBody Alumno alumno) {
		// Si no trae estatus, ponemos 'activo' por defecto
		if (alumno.getEstatus() == null || alumno.getEstatus().isEmpty()) {
			alumno.setEstatus("activo");
		}
		return repositorio.save(alumno);
	}

	// 3. CAMBIAR ESTATUS (PUT)
	@PutMapping("/alumnos/{id}/estatus")
	public ResponseEntity<?> cambiarEstatus(@PathVariable Long id, @RequestBody String nuevoEstatus) {
		Optional<Alumno> alumno = repositorio.findById(id);
		if (alumno.isPresent()) {
			Alumno a = alumno.get();
			// Limpiamos el string porque a veces llega con comillas extra
			a.setEstatus(nuevoEstatus.replace("\"", "").trim());
			repositorio.save(a);
			return ResponseEntity.ok().build();
		}
		return ResponseEntity.notFound().build();
	}
}

// --- ENTIDAD ---
@Entity
@Data
@Table(name = "alumnos")
class Alumno {
	@Id
	@GeneratedValue(strategy = GenerationType.IDENTITY)
	@Column(name = "id_alumno")
	private Long idAlumno;

	private String nombre;

	@Column(name = "ap_paterno")
	private String apPaterno;

	@Column(name = "ap_materno")
	private String apMaterno;

	@Column(name = "id_grupo")
	private Integer idGrupo;

	private String estatus;
}

// --- REPOSITORIO ---
@Repository
interface AlumnoRepository extends JpaRepository<Alumno, Long> {}