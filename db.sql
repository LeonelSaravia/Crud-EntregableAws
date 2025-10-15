CREATE TABLE motos (
    id INT IDENTITY(1,1) PRIMARY KEY,
    marca NVARCHAR(100) NOT NULL,
    modelo NVARCHAR(100) NOT NULL,
    año INT NOT NULL,
    cilindrada INT NOT NULL,
    color NVARCHAR(50),
    precio DECIMAL(10,2),
    tipo_moto NVARCHAR(20) CHECK (tipo_moto IN ('Deportiva', 'Naked', 'Custom', 'Scooter', 'Enduro', 'Adventure')),
    imagen NVARCHAR(255),
    fecha_creacion DATETIME DEFAULT GETDATE()
);

INSERT INTO motos (marca, modelo, año, cilindrada, color, precio, tipo_moto)
VALUES
('Honda', 'CBR 600RR', 2023, 600, 'Rojo', 12500.00, 'Deportiva'),
('Yamaha', 'MT-07', 2023, 689, 'Azul', 8500.00, 'Naked'),
('Kawasaki', 'Ninja 650', 2023, 649, 'Verde', 8999.00, 'Deportiva');


