def operation(op, a, b):
	if op == '+':
		return a + b
	elif op == '-':
		return a - b
	elif op == '*':
		return a * b
	elif op == '/':
		return a / b
	else:
		return -1

print(operation("0", 2, 6))