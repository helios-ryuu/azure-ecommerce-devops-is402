package vn.edu.uit.is402.users;

import jakarta.validation.Valid;
import jakarta.validation.constraints.Email;
import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.Size;
import java.util.Map;
import org.springframework.dao.DataIntegrityViolationException;
import org.springframework.http.*;
import org.springframework.security.crypto.bcrypt.BCryptPasswordEncoder;
import org.springframework.web.bind.annotation.*;

@RestController
@RequestMapping("/api/users")
public class UserController {
    private final UserRepository repository;
    private final BCryptPasswordEncoder encoder = new BCryptPasswordEncoder();

    public UserController(UserRepository repository) { this.repository = repository; }

    @PostMapping("/register")
    public ResponseEntity<?> register(@Valid @RequestBody RegisterRequest request) {
        if (repository.existsByUsernameOrEmail(request.username(), request.email())) {
            return ResponseEntity.status(HttpStatus.CONFLICT).body(Map.of("message", "Username hoặc email đã tồn tại"));
        }
        try {
            User user = repository.save(new User(request.username(), encoder.encode(request.password()), request.email()));
            return ResponseEntity.status(HttpStatus.CREATED).body(new UserResponse(user.getId(), user.getUsername(), user.getEmail()));
        } catch (DataIntegrityViolationException e) {
            return ResponseEntity.status(HttpStatus.CONFLICT).body(Map.of("message", "Username hoặc email đã tồn tại"));
        }
    }

    @PostMapping("/login")
    public ResponseEntity<?> login(@Valid @RequestBody LoginRequest request) {
        return repository.findByUsername(request.username())
            .filter(user -> encoder.matches(request.password(), user.getPasswordHash()))
            .<ResponseEntity<?>>map(user -> ResponseEntity.ok(new UserResponse(user.getId(), user.getUsername(), user.getEmail())))
            .orElseGet(() -> ResponseEntity.status(HttpStatus.UNAUTHORIZED).body(Map.of("message", "Sai tài khoản hoặc mật khẩu")));
    }

    @GetMapping("/verify/{id}")
    public ResponseEntity<?> verify(@PathVariable Long id) {
        return repository.findById(id)
            .<ResponseEntity<?>>map(user -> ResponseEntity.ok(Map.of("valid", true, "userId", user.getId())))
            .orElseGet(() -> ResponseEntity.notFound().build());
    }

    public record RegisterRequest(@NotBlank @Size(min=3,max=80) String username, @NotBlank @Size(min=8,max=100) String password, @NotBlank @Email String email) {}
    public record LoginRequest(@NotBlank String username, @NotBlank String password) {}
    public record UserResponse(Long id, String username, String email) {}
}
